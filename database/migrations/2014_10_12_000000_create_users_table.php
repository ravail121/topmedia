<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name',150);
            $table->string('username',50)->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('email',150)->index()->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('crypto_address')->index()->nullable();
            $table->string('location')->index()->nullable();
            $table->string('country_short_code',10)->nullable();
            $table->string('latitude',100)->nullable();
            $table->string('longitude',100)->nullable();
            $table->enum('type', ['admin', 'user'])->default('user');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->longText('reset_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
