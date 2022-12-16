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
        Schema::dropIfExists('home_sliders');
        Schema::create('home_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle');
            $table->string('link')->nullable();
            $table->mediumText('image');
            $table->foreignId('status_id')
                 ->nullable()
                 ->constrained('statuses')
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
        Schema::dropIfExists('home_sliders');
        Schema::enableForeignKeyConstraints();
    }
};
