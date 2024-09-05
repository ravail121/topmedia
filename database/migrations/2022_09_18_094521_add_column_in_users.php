<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text("about")->nullable()->after("longitude");
            $table->string("firebase_uid")->nullable()->after("about");
            $table->enum("profile_viewing",['private','public'])->default("public")->after("firebase_uid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("firebase_uid");
            $table->dropColumn("profile_viewing");
            $table->dropColumn("about");
        });
    }
}
