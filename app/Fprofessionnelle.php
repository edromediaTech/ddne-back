<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fprofessionnelle extends Model
{
  protected $fillable = ['titre','datef','duree','lieu','organisateur'];
}
