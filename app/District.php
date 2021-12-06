<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['nom','superficie','longitude','latitude','altitude'];

    public function departement(){
      return $this -> belongTo('App\Departement');
    }
    public function commune(){
      return $this->HasMany('App\Commune');
    }

}
