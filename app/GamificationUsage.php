<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamificationUsage extends Model
{
    protected $fillable = [
        'user_id',
        'gamification_id',
    ];
}
