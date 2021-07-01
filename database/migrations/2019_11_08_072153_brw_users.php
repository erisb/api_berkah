<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BrwUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brw_users', function (Blueprint $table) {
            $table->increments('brw_id');
            $table->string('username');
            $table->string('email');
            $table->string('password');
            $table->string('remember_token');
            $table->string('email_verif');
            $table->string('ref_number');
            $table->string('status');
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
        Schema::table('brw_users', function (Blueprint $table) {
            //
        });
    }
}
