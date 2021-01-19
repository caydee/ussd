<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongofthehoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songofthehours', function (Blueprint $table) {
            $table->id();
            $table->integer('menunumber');
            $table->string('name');
            $table->string('url')->nullable();
            $table->dateTime('date')->default(Carbon::now());

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('songofthehours');
    }
}
