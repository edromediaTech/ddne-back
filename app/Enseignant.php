<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
  protected $fillable = ['nom','prenom','adresse', 'nif', 'sexe', 'date_naissance', 'date_EFonction','cin', 'dept_n','dept_h', 'commune_n', 'commune_h', 'statutmat', 'lieunais','email'];
  public $primaryKey = 'id';
  protected $casts = ['id' => 'string'];
  protected $keyType = ['nif'=>'string'];
  public $incrementing = false;
    



  public function affectation (){
    return $this -> HasMany('App\Affectation');
  }

}
