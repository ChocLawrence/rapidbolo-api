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
        Schema::dropIfExists('requests');
        Schema::create('requests', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('description');
             $table->string('address');
             $table->mediumText('images')->nullable();
             $table->string('amount')->nullable();
             $table->decimal('longitude', 10, 8)->nullable();
             $table->decimal('latitude', 11, 8)->nullable();
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('user_id')
             ->constrained('users')
             ->onDelete('cascade');
             $table->foreignId('rating_id')
             ->constrained('ratings')
             ->onDelete('cascade'); 
             $table->foreignId('service_id')
             ->constrained('services')
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
        Schema::dropIfExists('requests');
        Schema::enableForeignKeyConstraints();
    }
};
