<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
      protected $fillable = ['responsable_id', 'message','lu'];
}
