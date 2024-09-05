<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger("push_type");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("from_user_id");
            $table->string("push_title");
            $table->string("push_message");
            $table->unsignedBigInteger("object_id");
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_lists');
    }
}
