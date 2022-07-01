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
        Schema::create('database_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id');
            $table->string('host');
            $table->integer('port');
            $table->string('login');
            $table->string('pass');
            $table->boolean('availability');
            $table->string('table');
            $table->json('error')->nullable()->default(null);
            $table->timestamps();
            $table->foreign('connection_id')
                ->references('id')
                ->on('connections')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('database_connections', function (Blueprint $table) {
            $table->dropForeign('connection_id');
        });
        Schema::dropIfExists('database_connections');
    }
};
