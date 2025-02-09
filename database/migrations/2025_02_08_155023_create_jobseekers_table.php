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
        Schema::create('jobseekers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('college_id')
                  ->nullable()
                  ->references('id')
                  ->on('users')
                  ->where('role', 'college')
                  ->onDelete('set null');
            $table->text('image')->nullable();
            $table->string('mobile');
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('current_address');
            $table->string('permanent_address');
            $table->string('linkedin_url')->nullable();
            $table->text('professional_summary')->nullable();
            $table->json('skills')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobseekers');
    }
};
