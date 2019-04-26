<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
			$table->string('avatar')->default('');
			$table->tinyInteger('gender')->default(0);
			$table->smallInteger('level')->default(0);
			$table->string('location')->default('');
			$table->smallInteger('birthyear')->unsigned()->default(0);
			$table->tinyInteger('birthmonth')->unsigned()->default(0);
			$table->tinyInteger('birthday')->unsigned()->default(0);
			$table->string('bloodtype')->default('');
			$table->string('height')->default('');
			$table->string('weight')->default('');
			$table->string('signature')->default()->nullable();
			$table->string('banner')->default('');
            $table->tinyInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_profile');
    }
}
