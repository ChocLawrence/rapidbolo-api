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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ratings');
        Schema::create('ratings', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('value');
             $table->string('comment')->nullable();
             $table->foreignId('user_id')
             ->constrained('users')
             ->onDelete('cascade');
             $table->foreignId('request_id')
             ->constrained('requests')
             ->onDelete('cascade');
             $table->timestamps();
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ratings');
        Schema::enableForeignKeyConstraints();
    }
};
