<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->text('password');
            $table->string('profile', 255)->nullable();
            $table->string('type', 1)->default('1'); // Default to '1' (User)
            $table->string('phone', 20)->nullable()->unique();
            $table->string('address', 255)->nullable();
            $table->date('dob')->nullable();
            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('updated_user_id');
            $table->unsignedBigInteger('deleted_user_id')->nullable();
            $table->rememberToken();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('create_user_id')->references('id')->on('users');
            $table->foreign('updated_user_id')->references('id')->on('users');
            $table->foreign('deleted_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
