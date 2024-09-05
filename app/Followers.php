<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(new User(),"profile_id");
    }
}
