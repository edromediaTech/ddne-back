<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inspecteur_zone extends Model
{
    protected $fillable = ['inspecteur_id','date_affectation','zone_id'];
}
