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
        Schema::dropIfExists('subscriptions');
        Schema::create('subscriptions', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('start_date');
             $table->string('end_date');
             $table->foreignId('user_id')
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
        Schema::dropIfExists('subscriptions');
        Schema::enableForeignKeyConstraints();
    }
};
