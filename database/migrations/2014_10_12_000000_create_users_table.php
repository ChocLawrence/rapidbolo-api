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
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('middlename')->nullable();
            $table->string('username')->unique();
            $table->string('dob')->nullable();
            $table->enum('gender',['male','female']);
            $table->mediumText('image')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('country_id')
            ->nullable()
            ->constrained('countries')
            ->onDelete('cascade');  
            $table->string('email')->unique();
            $table->text('bio')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); 
            $table->decimal('longitude', 10, 8)->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->foreignId('status_id')
                 ->default(1)
                 ->nullable()
                 ->constrained('statuses')
                 ->onDelete('cascade');   
            $table->foreignId('role_id')
                 ->default(2)
                 ->constrained('roles')
                 ->onDelete('cascade'); 
            $table->foreignId('plan_id')
                 ->nullable()
                 ->constrained('plans')
                 ->onDelete('cascade');      
            $table->rememberToken();
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
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};
