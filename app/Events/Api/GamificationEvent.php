<?php

namespace App\Events\Api;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GamificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $type;

    public function __construct(User $user, $type)
    {
        $this->user = $user;
        $this->type = $type;
    }


}
