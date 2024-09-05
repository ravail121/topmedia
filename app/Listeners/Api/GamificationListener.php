<?php

namespace App\Listeners\Api;

use App\Gamification;
use App\GamificationUsage;
use App\utility\Coin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GamificationListener implements ShouldQueue
{

    public function handle($event)
    {
        $user = $event->user;
        if ($user) {
            if ($user->crypto_address) {
                $type = $event->type;
                $dynamic_count = $user->$type->count();
                $data = Gamification::ForFresh($user)->where('relation_ship', $type)->range($dynamic_count)->first();
                if ($data && $dynamic_count >= $data->max_range) {
                    $coin = $data->coin;
                    $CoinLib = new Coin();
                    $is_coin_sent = $CoinLib->GiftToken($user->crypto_address, $coin);
                    if ($is_coin_sent) {
                        $data->usage()->create(['user_id' => $user->id]);
                        $push = __("You Just crossed :count :value,you :coin deposited inside crypto wallet", ['count' => $dynamic_count, 'coin' => $coin, 'value' => $data->name]);
                        send_push($user->id, [
                            'user_id' => $user->id,
                            'from_user_id' => $user->id,
                            'push_type' => 11,
                            'push_title' => $push,
                            'push_message' => $push,
                        ]);
                    }
                }
            } else {
                send_push($user->id, [
                    'user_id' => $user->id,
                    'from_user_id' => $user->id,
                    'push_type' => 10,
                    'push_title' => "Please Setup Your Crypto Wallet for receiving coins",
                    'push_message' => "Please Setup Your Crypto Wallet for receiving coins",
                ]);
            }
        }
    }


}
