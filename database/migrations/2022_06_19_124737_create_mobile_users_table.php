<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_etmobile')->create('mobile_users', function (Blueprint $table) {
            $table->id();
            $table->string('login', '50')->unique();
            $table->string('password', '512')->default('none');
            $table->string('firstname', '30')->default('none');
            $table->string('lastname', '30')->default('none');
            $table->string('middlename', '30')->default('');
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
        Schema::dropIfExists('mobile_users');
    }
}
