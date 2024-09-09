<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgoraTokensTable extends Migration
{
    public function up()
    {
        Schema::create('agora_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('agora_token');
            $table->string('channel_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agora_tokens');
    }
}
