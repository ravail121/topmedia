<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostLikes extends Model
{
    protected $guarded = [];

    function post()
    {
        return $this->belongsTo(Posts::class, "post_id", "id")->with("user");
    }
}
