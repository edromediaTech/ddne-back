<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
  protected $fillable = ['nom','superficie','longitude','altitude','altitude'];
  public $primaryKey = 'id';
  protected $casts = ['id' => 'string'];
    public $incrementing = false;
    
  public function district(){
    return $this ->HasMany('App\District');


  }
}
