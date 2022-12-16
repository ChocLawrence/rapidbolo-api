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
        Schema::dropIfExists('verifications');
        Schema::create('verifications', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->mediumText('front_id')->nullable();
             $table->mediumText('back_id')->nullable();
             $table->mediumText('passport')->nullable(); 
             $table->mediumText('selfie')->nullable();
             $table->mediumText('selfie_with_id')->nullable();
             $table->mediumText('degree')->nullable();
             $table->mediumText('certificate')->nullable();
             $table->mediumText('attestation')->nullable();
             $table->foreignId('status_id')
             ->constrained('statuses')
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
        Schema::dropIfExists('verifications');
        Schema::enableForeignKeyConstraints();
    }
};
