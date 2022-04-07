<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $fillable = ['enseignant_id', 'type', 'date_nomination', 'code_budgetaire'];
}
