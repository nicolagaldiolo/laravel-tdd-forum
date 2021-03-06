<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('channel_id');
            $table->unsignedBigInteger('replies_count')->default(0);
            $table->unsignedBigInteger('visits')->default(0);
            $table->unsignedBigInteger('best_reply_id')->nullable();
            $table->string('title');
            $table->boolean('locked')->default(false);
            $table->text('body');
            $table->timestamps();

            $table->foreign('best_reply_id')->references('id')->on('replies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('threads');
    }
}
