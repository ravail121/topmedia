<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(General_Setting_Seeder::class);
        $this->call(Users_Seeder::class);
        $this->call(ContentSeeder::class);
        $this->call(GamificationSeeder::class);
    }
}
