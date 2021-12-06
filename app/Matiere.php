<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    protected $fillable = ['enseignant_id','ecole_id','classe','date_affectation'];
}
