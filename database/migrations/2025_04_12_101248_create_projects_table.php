<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
            $table->enum('type_of_project', ['internship', 'full-time', 'part-time', 'contract']);
            $table->enum('status', ['active', 'closed', 'draft'])->default('active');
            $table->json('requirements')->nullable();
            $table->unsignedBigInteger('project_category_id');
            $table->json('skills_required')->nullable();
            $table->string('location');
            $table->integer('salary')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->foreign('project_category_id')->references('id')->on('project_categories')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
}; 