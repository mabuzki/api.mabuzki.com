<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatTableComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('articleid');
            $table->string('author');
            $table->string('authorid');
            $table->mediumText('comment');
            
            $table->string('favtimes')->default(0);
            $table->string('replynum')->default(0);
			
            $table->string('date_post');
            
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
        //
        Schema::dropIfExists('comments');
    }
}
