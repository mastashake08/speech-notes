<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Note Dashboard</div>

                    <div class="card-body">
                        <button v-if="isRecording == false"class="btn btn-sm btn-primary" v-on:click="startRecording()">Record</button>
                        <button v-else class="btn btn-sm btn-danger" v-on:click="stopRecording()">Stop</button>
                        <br>
                        <select class="form-control" v-model="voice">
                          <option v-for="v in speechSynthesis.getVoices()" v-bind:value="v.name">{{v.name}} - ({{v.lang}})</option>
                        </select>
                        <br>
                        <div class="slidecontainer">
                            <label for="rate">Rate</label>
                            <input class="slider" type="range" id="rate"  min="0.1" max="10"  v-model="rate">
                          </div>
                          <div class="slidecontainer">
                              <label for="pitch">Pitch</label>
                              <input class="slider" type="range" id="pitch"  min="0" max="2"  v-model="pitch">
                            </div>
                        <br>
                        {{transcipt}}
                        <hr>
                        <ul>
                          <li v-for="note in notes" v-on:click="sayNote(note)">{{note.content}} </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data(){
          return{
            notes:[],
            interimTranscript: '',
            transcipt: '',
            isRecording: false,
            speechRecog: {},
            speechSynthesis: {},
            voice: null,
            rate: 2,
            pitch: 1
          }
        },
        created(){
          var that = this;
          axios.get('/api/note').then(function(data){
            that.notes = data.data;
          });
          this.speechSynthesis = window.speechSynthesis;
          this.speechRecog = new webkitSpeechRecognition();
          this.speechRecog.continuous = false;
          this.speechRecog.interimResults = true;
          this.speechRecog.onstart = function() {
            that.isRecording = true;
          }

          this.speechRecog.onresult = function(event) {
            that.interimResults = '';
            var last = event.results.length -1;


              if (event.results[last].isFinal) {
               this.transcript = event.results[last][0].transcript;
               this.transcript= this.transcript.charAt(0).toUpperCase() + this.transcript.substr(1);
             }




          }
          this.speechRecog.onerror = function(event) {
            console.log(event);
           }

          this.speechRecog.onend = function() {
            that.isRecording = false;
            var data = {content: this.transcript};
            axios.post('/api/note',data).then(function(data){
              that.notes.push(data.data);
              that.transcipt = '';
              that.interimResults = '';
            });
          }
        },
        methods:{
          startRecording:function(){
            this.speechRecog.start();
          },
          stopRecording:function(){
            this.speechRecog.stop();
          },
          sayNote:function(note){
            var msg = new SpeechSynthesisUtterance(note.content);
            if(this.voice != null){
              var that = this;
              msg.voice = speechSynthesis.getVoices().filter(function(voice) { return voice.name == that.voice; })[0];
              msg.pitch = this.pitch;
              msg.rate = this.rate;
            }

            console.log(msg);
            this.speechSynthesis.speak(msg);
          }

        }
    }
</script>
