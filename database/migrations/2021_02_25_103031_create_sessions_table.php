<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('SESSION_ID');
            $table->string('SERVICE_CODE');
            $table->string('MSISDN');
            $table->string('USSD_STRING');
            $table->integer('LEVEL');
            $table->text('MENU');
            $table->integer('SELECTION');
            $table->integer('MIN_VAL');
            $table->integer('MAX_VAL');
            $table->dateTime('SESSION_DATE')->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
