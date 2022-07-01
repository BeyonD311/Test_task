<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('last_update', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('database_connection_id');
            $table->unsignedBigInteger('server_connection_id');
            $table->timestamp('update');
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('last_update');
    }
};
