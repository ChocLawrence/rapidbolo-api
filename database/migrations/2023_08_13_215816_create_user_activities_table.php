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
        Schema::dropIfExists('user_activities');
        Schema::create('user_activities', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->boolean('online')->default(false);
             $table->enum('state',['login','logout','reconnect','lostcon']);
             $table->string('report')->nullable();
             $table->decimal('longitude', 10, 8)->nullable();
             $table->decimal('latitude', 11, 8)->nullable();
             $table->foreignId('user_id')
             ->nullable()
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
        Schema::dropIfExists('user_activities');
        Schema::enableForeignKeyConstraints();
    }
};
