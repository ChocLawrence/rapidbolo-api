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
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('name');
             $table->string('amount');
             $table->string('sender_phone')->nullable();
             $table->string('sender_name')->nullable();
             $table->string('receiver_phone')->nullable();
             $table->string('receiver_name')->nullable();
             $table->string('description');
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade'); 
             $table->foreignId('transaction_id')
             ->constrained('transaction_types')
             ->onDelete('cascade');
             $table->foreignId('payment_method_id')
             ->constrained('payment_methods')
             ->onDelete('cascade');
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
        Schema::dropIfExists('transactions');
        Schema::enableForeignKeyConstraints();
    }
};
