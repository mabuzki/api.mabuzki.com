<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('author');
            $table->string('authorid');
            $table->string('type');
            $table->string('subject');
            $table->string('location')->default('');
            $table->string('cover');
            $table->mediumText('content');
            $table->string('attachment');

            $table->string('tags');
            
            $table->string('readtimes')->default(0);
            $table->string('favtimes')->default(0);
            $table->string('replynum')->default(0);
			
            $table->string('date_post');
            $table->string('date_update');
            
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
        Schema::dropIfExists('articles');
    }
}
