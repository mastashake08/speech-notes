<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    //
    public $fillable = ['content'];

    public function user(){
      return $this->belongsTo('App\User');
    }
}
