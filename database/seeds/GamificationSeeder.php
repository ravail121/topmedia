<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamificationSeeder extends Seeder
{

    public function run()
    {
        $data = [
            [
                'name' => 'followers',
                'min_range' => '0',
                'max_range' => '100',
                'coin' => '10',
                'relation_ship' => 'followers',
            ],
            [
                'name' => 'likes',
                'min_range' => '0',
                'max_range' => '100',
                'coin' => '10',
                'relation_ship' => 'post_likes',
            ],
            [
                'name' => 'comments',
                'min_range' => '0',
                'max_range' => '100',
                'coin' => '10',
                'relation_ship' => 'post_comments',
            ],
            [
                'name' => 'followers',
                'min_range' => '101',
                'max_range' => '200',
                'coin' => '10',
                'relation_ship' => 'followers',
            ],
            [
                'name' => 'likes',
                'min_range' => '101',
                'max_range' => '200',
                'coin' => '10',
                'relation_ship' => 'post_likes',
            ],
            [
                'name' => 'comments',
                'min_range' => '101',
                'max_range' => '200',
                'coin' => '10',
                'relation_ship' => 'post_comments',
            ],
            [
                'name' => 'followers',
                'min_range' => '201',
                'max_range' => '300',
                'coin' => '10',
                'relation_ship' => 'followers',
            ],
            [
                'name' => 'likes',
                'min_range' => '201',
                'max_range' => '300',
                'coin' => '10',
                'relation_ship' => 'post_likes',
            ],
            [
                'name' => 'comments',
                'min_range' => '201',
                'max_range' => '300',
                'coin' => '10',
                'relation_ship' => 'post_comments',
            ],
        ];
        \App\Gamification::insert($data);
    }
}
