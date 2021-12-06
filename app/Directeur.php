<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Directeur extends Model
{
  protected $fillable = ['nomd','prenom','teld','telephoned','emaild', 'adressed', 'datenais',
                            'lieunais','nif', 'cin','section_communaled_id', 'sexe'];

    public $primaryKey = 'id';
    protected $casts = ['id' => 'string'];
    public $incrementing = false;

    public function section_communale(){
      return $this ->belongTo('App\Section_Communale');
    }

    public function ecole(){
      return $this ->hasOne('App\Ecole');
    }
}
