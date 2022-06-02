<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->default(null);
            $table->string('password')->nullable();
//            $table->string('country_code')->default(null);
//            $table->string('mobile')->default(null);
            $table->string('profile_image')->nullable()->default(null);
            $table->string('email')->default(null);
            $table->enum('type', ['admin', 'instructor','student'])->default('student');
            $table->string('approx_hours')->default(0);
            $table->double('experience_in_year')->default(0);
            $table->double('rate_per_hour')->default(0);
            $table->unsignedBigInteger('airport_id')->nullable();
            $table->foreign('airport_id')->references('id')->on('airports');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->longText('reset_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
