<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inspecteur extends Model
{
    protected $fillable = ['nom','prenom','nif'];
    public $primaryKey = 'id';
    protected $casts = ['id' => 'string'];
    public $incrementing = false;


        public function zones()
        {
          return $this->belongsToMany('App\Zone')->withTimestamps();
        }

}
