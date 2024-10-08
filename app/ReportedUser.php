<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReportedUser extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'profile_id', 'id');
    }
}
