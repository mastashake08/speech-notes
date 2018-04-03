<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Note Dashboard</div>

                    <div class="card-body">
                        <button v-if="isRecording == false"class="btn btn-sm btn-primary" v-on:click="startRecording()">Record</button>
                        <button v-else class="btn btn-sm btn-danger" v-on:click="stopRecording()">Stop</button>
                        <hr>
                        <ul>
                          <li v-for="note in notes">{{note.content}}</li>
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
            speechRecog: {}
          }
        },
        created(){
          var that = this;
          axios.get('/api/note').then(function(data){
            that.notes = data.data;
          });
          that.speechRecog = new webkitSpeechRecognition();
          that.speechRecog.continuous = true;
          that.speechRecog.interimResults = true;
          that.speechRecog.onstart = function() {
            that.isRecording = true;
          }

          that.speechRecog.onresult = function(event) {
            that.interimResults = '';
            for (var i = event.resultIndex; i < event.results.length; ++i) {
             if (event.results[i].isFinal) {
               that.transcript += event.results[i][0].transcript;
             } else {
               that.interimTranscript += event.results[i][0].transcript;
             }
           }
           that.transcript = capitalize(that.transcript);

          }
          that.speechRecog.onerror = function(event) {
            console.log(event);
           }

          that.speechRecog.onend = function() {
            that.isRecording = false;
            var data = {content: that.transcript};
            axios.post('/api/note',data).then(function(data){
              that.notes.push(data.data);
            });
          }
        },
        methods:{
          startRecording:function(){
            this.speechRecog.start();
          },
          stopRecording:function(){
            this.speechRecog.stop();
          }

        }
    }
</script>
