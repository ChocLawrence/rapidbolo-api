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
        Schema::dropIfExists('status_histories');
        Schema::create('status_histories', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('note');
             $table->foreignId('status_id')
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('demand_id')
             ->nullable()
             ->constrained('demands')
             ->onDelete('cascade');
             $table->foreignId('service_id')
             ->nullable()
             ->constrained('services')
             ->onDelete('cascade');
             $table->string('suspension_days')->nullable();
             $table->string('suspension_exp')->nullable();
             $table->string('user_marked_date')->nullable(); 
             $table->string('user_marked_exp_date')->nullable();
             $table->string('strike_count')->default(0)->nullable();
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
        Schema::dropIfExists('status_histories');
        Schema::enableForeignKeyConstraints();
    }
};
