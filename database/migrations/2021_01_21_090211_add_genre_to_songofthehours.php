<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenreToSongofthehours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('songofthehours', function (Blueprint $table) {
            $table->string('genre')->default('Song of The hour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('songofthehours', function (Blueprint $table) {
            $table->dropColumn('genre');
        });
    }
}
