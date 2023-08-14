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
        Schema::dropIfExists('demands');
        Schema::create('demands', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('description');
             $table->string('address'); 
             $table->mediumText('images')->nullable();
             $table->string('p_stages')->default(1);
             $table->string('p1_amount')->nullable();
             $table->string('p2_amount')->nullable();
             $table->boolean('d_cfm_amount')->default(false);
             $table->boolean('p_cfm_amount')->default(false);
             $table->string('d_note')->nullable();
             $table->string('p_note')->nullable();
             $table->string('deadline')->nullable();
             $table->string('delivery_date')->nullable();
             $table->decimal('longitude', 10, 8)->nullable();
             $table->decimal('latitude', 11, 8)->nullable();
             $table->foreignId('status_id')
             ->nullable()
             ->constrained('statuses')
             ->onDelete('cascade');
             $table->foreignId('user_id')
             ->constrained('users')
             ->onDelete('cascade');
             $table->foreignId('rating_id')
             ->nullable()
             ->constrained('ratings')
             ->onDelete('cascade'); 
             $table->foreignId('service_id')
             ->constrained('services')
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
        Schema::dropIfExists('demands');
        Schema::enableForeignKeyConstraints();
    }
};
