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
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('name');
             $table->string('slug')->unique();
             $table->string('description')->nullable();
             $table->mediumText('image')->nullable();
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('created_by')
             ->constrained('users')
             ->onDelete('cascade');
             $table->timestamps();
         });
         Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('categories');
        Schema::enableForeignKeyConstraints();
    }
};
