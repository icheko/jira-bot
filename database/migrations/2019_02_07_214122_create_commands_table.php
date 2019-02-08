<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('comment_id');
            $table->uuid('command_type_id');
            $table->string('arguments')->nullable(true);
            $table->boolean('processed')->default(false);
            $table->timestamps();
            $table->primary('id');
            $table->foreign('comment_id')->references('id')->on('comments');
            $table->foreign('command_type_id')->references('id')->on('command_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commands');
    }
}
