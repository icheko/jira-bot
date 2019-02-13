<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonitorCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitor_commands', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('command_id');
            $table->string('bamboo_build_key');
            $table->tinyInteger('retries')->default(30);
            $table->boolean('complete')->default(false);
            $table->timestamps();
            $table->primary('id');
            $table->foreign('command_id')->references('id')->on('commands');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitor_commands');
    }
}
