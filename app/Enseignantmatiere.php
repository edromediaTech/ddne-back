<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enseignantmatiere extends Model
{
    protected $fillable = ['matiere_id','affectation_id','nb_heure'];
}
