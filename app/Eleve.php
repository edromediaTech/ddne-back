<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
     protected $fillable = ['nom','prenom','sexe', 'datenais','dept_n','lieunais','deficience','tel_persrep','prenom_mere'];
  public $primaryKey = 'id';
  protected $casts = ['id' => 'string'];
    public $incrementing = false;
    
}
