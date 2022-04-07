<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rep_etablie extends Model
{
    protected $fillable = ['questionnaire_id','reponse'];
}
