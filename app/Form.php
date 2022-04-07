<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['titre','description', 'objectif', 'date_debut', 'date_fin'];
}
