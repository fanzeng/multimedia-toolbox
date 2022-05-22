<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frames', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order');
            $table->integer('num_repetition');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('video_recipe_id');
            $table->string('src_filename');
            $table->index('user_id');
            $table->index('video_recipe_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frames');
    }
}
