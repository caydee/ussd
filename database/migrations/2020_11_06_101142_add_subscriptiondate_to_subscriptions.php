<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptiondateToSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dateTime('subscriptiondate')->nullable();
            $table->dateTime('subscriptionexpirydate')->nullable();
            $table->tinyInteger('renewed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['subscriptiondate']);
            $table->dropColumn(['subscriptionexpirydate']);
            $table->dropColumn(['renewed']);
        });
    }
}
