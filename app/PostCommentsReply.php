<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostCommentsReply extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(new User(), "user_id")
            ->select("id", "name", "username", "profile_image")->withTrashed();
    }
}
