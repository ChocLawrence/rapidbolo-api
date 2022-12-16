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
        Schema::dropIfExists('plans');
        Schema::create('plans', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('name');
             $table->string('duration');
             $table->text('description');
             $table->string('price')->nullable();
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('created_by')
             ->nullable()
             ->constrained('users')
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
        Schema::dropIfExists('plans');
        Schema::enableForeignKeyConstraints();
    }
};
