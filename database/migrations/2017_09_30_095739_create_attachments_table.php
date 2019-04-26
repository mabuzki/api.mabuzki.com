<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('photos', function (Blueprint $table) {
			$table->increments('id');
			$table->string('type');
			$table->string('userid');
			$table->string('username');
			$table->string('filepath')->default('');
			$table->string('filename')->default('');
			//$table->string('caption')->default('');
			$table->string('salt')->default('');
			$table->string('suffix')->default('');
			$table->string('animated')->default('');
			$table->smallInteger('width')->unsigned()->default(0);
			$table->smallInteger('height')->unsigned()->default(0);
			$table->string('postip')->default('');
			$table->string('upload_time')->default('');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('photos');
	}
}
