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
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('payment_verified')->default(false)->after('deadline');
            $table->string('payment_transaction_id')->nullable()->after('payment_verified');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['payment_verified', 'payment_transaction_id', 'payment_amount']);
        });
    }
};
