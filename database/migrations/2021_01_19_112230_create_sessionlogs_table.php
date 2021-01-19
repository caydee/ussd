<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessionlogs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('msisdn');
            $table->string('ussd_string');
            $table->string('service_code');
            $table->dateTime('session_date')->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessionlogs');
    }
}
