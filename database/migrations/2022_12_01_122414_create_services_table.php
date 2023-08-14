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
        Schema::dropIfExists('services');
        Schema::create('services', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('address');
             $table->decimal('longitude', 10, 8)->nullable();
             $table->decimal('latitude', 11, 8)->nullable();
             $table->string('service_code')->unique()->nullable();
             $table->string('description')->nullable();
             $table->mediumText('images')->nullable();
             $table->string('experience')->nullable();
             $table->string('completed')->nullable();
             $table->string('rating')->nullable();
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('payment_pref_id')
             ->nullable()
             ->constrained('payment_preferences')
             ->onDelete('cascade');
             $table->foreignId('user_id')
             ->constrained('users')
             ->onDelete('cascade');
             $table->foreignId('category_id')
             ->constrained('categories')
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
        Schema::dropIfExists('services');
        Schema::enableForeignKeyConstraints();
    }
};
