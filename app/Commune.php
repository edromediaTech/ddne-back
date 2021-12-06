<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
  protected $fillable = ['nom','superficie','district_id','longitude','latitude','altitude'];

  public function district(){
    return $this->belongTo('App\District');
  }

  public function zone(){
    return $this->HasMany('App\Zone');
  }

  public function section_communale(){
    return $this->HasMany('App\Section_Communale');
  }

  public function getOrderNoAttribute() {
    return str_pad($this->id,4,'0',STR_PAD_LEFT);
}

}
