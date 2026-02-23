<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ride_id')->nullable()->constrained()->onDelete('set null');

            // Type & Provider
            $table->enum('type', [
                'ride_payment',
                'driver_credit',
                'withdrawal',
                'refund',
                'top_up'
            ]);

            $table->enum('provider', [
                'peex',
                'mtn_momo',
                'airtel_money',
                'stripe',
                'internal'
            ]);

            $table->string('provider_transaction_id')->nullable();

            // Amounts
            $table->decimal('amount', 12, 2);
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('driver_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('XAF');

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'escrowed'
            ])->default('pending');

            // Provider data
            $table->json('provider_response')->nullable();
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
            $table->index('provider_transaction_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
