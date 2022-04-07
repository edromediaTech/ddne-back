<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Funiversitaire extends Model
{
  protected $fillable = ['nomf','date_debut','date_fin','lieu'];
}
