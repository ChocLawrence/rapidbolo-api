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
        Schema::dropIfExists('chats');
        Schema::create('chats', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('message');
             $table->mediumText('images')->nullable();
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('sender_user_id')
             ->constrained('users')
             ->onDelete('cascade');
             $table->foreignId('receiver_user_id')
             ->constrained('users')
             ->onDelete('cascade'); 
             $table->foreignId('demand_id')
             ->constrained('demands')
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
        Schema::dropIfExists('chats');
        Schema::enableForeignKeyConstraints();
    }
};
