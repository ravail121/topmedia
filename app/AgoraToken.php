<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgoraToken extends Model
{

    protected $fillable = [
        'uid',
        'agora_token',
        'channel_name'
    ];
}
