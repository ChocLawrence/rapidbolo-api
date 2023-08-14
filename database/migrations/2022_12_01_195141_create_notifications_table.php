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
        Schema::dropIfExists('notifications');
        Schema::create('notifications', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('summary');
             $table->string('message');
             $table->string('url')->nullable();
             $table->foreignId('label_id')
             ->nullable()
             ->constrained('labels')
             ->onDelete('cascade');
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('sender_user_id')
             ->constrained('users')
             ->onDelete('cascade');
             $table->foreignId('receiver_user_id')
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
        Schema::dropIfExists('notifications');
        Schema::enableForeignKeyConstraints();
    }
};
