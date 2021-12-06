<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section_communale extends Model
{
  protected $fillable = ['id','nom', 'commune_id', 'superficie', 'altitude', 'latitude', 'longitude'];

  protected $keyType = 'string';
    public $incrementing = false;
    
  public function commune(){
  return $this->belongTo('App\Commune');
  }

  public function ecole(){
    return $this->HasMany('App\Ecole');
  }

  public function directeur(){
    return $this->HasMany('App\Directeur');
  }


}
