<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ecole extends Model
{
  protected $fillable = ['email','nom','adresse','sigle','tel','telephone','fondateur',
  'secteur','milieu','location','statut','section_communale_id','zone_id','longitude','latitude',
  'altitude','code'];
  public $primaryKey = 'id';
  protected $casts = ['id' => 'string'];
  public $incrementing = false;


  public function zone(){
    return $this ->belongTo('App\Zone');
  }

  public function section_communale(){
    return $this ->belongTo('App\Section_Communale');
  }

  public function directeur(){
    return $this ->hasOne('App\Directeur');
  }




}
