<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
     protected $fillable = ['moyenne','mention','classeleve_id'];


     public function setmoyenneAttribute($value)
    {
        $this->attributes['moyenne']=number_format((float)$value, 2, '.', '');
    }

    
        
    
}

