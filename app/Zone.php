<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['nom','superficie','longitude','latitude','altitude'];

    public function ecole(){
      return $this -> HasMany('App\Ecole');
    }

    public function commune(){
      return $this->belongTo('App\Commune');
    }

    public function inspecteurs()
    {
      return $this->belongsToMany('App\Inspecteur')->withTimestamps();
    }

}
