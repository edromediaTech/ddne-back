<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Insprincipal extends Model
{
    protected $fillable = ['nom', 'prenom', 'nif', 'user_id', 'district_id','tel', 'type'];
}
