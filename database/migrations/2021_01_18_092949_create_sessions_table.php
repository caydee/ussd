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
            $table->string('session_id');
            $table->string('msisdn');
            $table->string('ussd_string');
            $table->string('service_code');
            $table->string('menus');
            $table->integer('ussd_level')->default(0);
            $table->string('current_selection')->default('');
            $table->integer('expected_input')->default(0);//0 - Numeric, 1 - Non-numeric
            $table->integer('min_selection')->default(0);
            $table->integer('max_selection')->default(0);
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
        Schema::dropIfExists('sessions');
    }
}
