<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
  protected $fillable = ['enseignant_id','ecole_id','classe_id','date_affectation'];

  public function enseignant(){
    return $this -> belongTo('App\Enseignant');
  }
}
