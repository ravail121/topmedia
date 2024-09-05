<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationList extends Model
{
    protected $guarded = [];

    function scopeSimpleDetails()
    {
        return $this->select("id", "push_type", "user_id", "from_user_id", "push_title", "push_message", "object_id");
    }

    function from_user()
    {
        return $this->belongsTo(new User(), "from_user_id")->select("id", "name", "profile_image");
    }
}
