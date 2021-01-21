<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenreToTopgospels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('topgospels', function (Blueprint $table) {
            $table->string('genre')->default('Top Gospel Songs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('topgospels', function (Blueprint $table) {
            $table->dropColumn('genre');
        });
    }
}
