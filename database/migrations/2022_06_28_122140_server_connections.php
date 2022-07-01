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
        Schema::create('server_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id');
            $table->string('host');
            $table->integer('port');
            $table->string('login');
            $table->string('pass');
            $table->boolean('availability');
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
        Schema::table('server_connections', function (Blueprint $table) {
            $table->dropForeign('connection_id');
        });
        Schema::dropIfExists('server_connections');
    }
};
