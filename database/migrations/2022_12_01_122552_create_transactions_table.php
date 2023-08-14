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
             $table->string('description');
             $table->string('amount');
             $table->foreignId('country_id')
             ->nullable()
             ->constrained('countries')
             ->onDelete('cascade');
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade'); 
             $table->foreignId('transaction_type_id')
             ->constrained('transaction_types')
             ->onDelete('cascade');
             $table->foreignId('payment_method_id')
             ->constrained('payment_methods')
             ->onDelete('cascade');
             $table->foreignId('demand_id')
             ->nullable()
             ->constrained('demands')
             ->onDelete('cascade');
             $table->foreignId('user_id')
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
        Schema::dropIfExists('transactions');
        Schema::enableForeignKeyConstraints();
    }
};
