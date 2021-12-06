<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
     protected $fillable = ['decision_id','classeleve_id','valider', 'ecolecible'];
}
