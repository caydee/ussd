<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirtimerequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airtimerequests', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('msisdn');
            $table->string('creditphone')->nullable();
            $table->decimal('amount')->default(0);
            $table->dateTime('timein')->default(now());
            $table->smallInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airtimerequests');
    }
}
