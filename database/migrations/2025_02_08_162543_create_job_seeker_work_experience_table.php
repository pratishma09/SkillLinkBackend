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
        Schema::create('job_seeker_work_experiences', function (Blueprint $table) {
        $table->id();
            $table->unsignedBigInteger('jobseeker_id');
            $table->string('title');
            $table->string('company_name');
            $table->date('joined_date');
            $table->date('end_date')->nullable();
            $table->boolean('currently_working');
            $table->timestamps();

            $table->foreign('jobseeker_id')
                ->references('id')
                ->on('jobseekers')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeker_work_experiences');
    }
};
