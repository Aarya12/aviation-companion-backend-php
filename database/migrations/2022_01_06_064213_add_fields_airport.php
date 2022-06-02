<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsAirport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('airports', function (Blueprint $table) {
            $table->string('unq_id')->nullable();
            $table->string('ident')->nullable();
            $table->string('local_code')->nullable();
            $table->string('gps_code')->nullable();
            $table->string('iata_code')->nullable();
            $table->string('type')->nullable();
            $table->string('iso_country')->nullable();
            $table->string('iso_region')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
