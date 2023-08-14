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
        Schema::dropIfExists('payment_preferences');
        Schema::create('payment_preferences', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('phone')->unique()->nullable();
             $table->foreignId('service_id')
             ->constrained('services')
             ->onDelete('cascade');
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('payment_m_id')
             ->constrained('payment_methods')
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
        Schema::dropIfExists('payment_preferences');
        Schema::enableForeignKeyConstraints();
    }
};
