<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ecoleresponsable extends Model
{
     protected $fillable = ['user_id', 'ecole_id', 'niveau', 'nif', 'valider'];
}
