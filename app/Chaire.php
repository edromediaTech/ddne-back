<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chaire extends Model
{
    protected $fillable = ['enseignant_id','type_chaire'];
}
