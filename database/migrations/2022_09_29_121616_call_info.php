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
        Schema::create('call_info', function (Blueprint $table) {
            $table->unsignedBigInteger('file_id');
            $table->foreign("file_id")
                ->on('files')
                ->references("id")
                ->cascadeOnDelete();
            $table->string("src");
            $table->string("dst");
            $table->string("duration");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("call_info", function (Blueprint $table) {
            $table->dropForeign('file_id');
        });
        Schema::dropIfExists('call_info');
    }
};
