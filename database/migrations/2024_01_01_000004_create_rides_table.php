<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');

            // Locations
            $table->string('pickup_address');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('dropoff_address');
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 11, 8);

            // Trip details
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('price_per_km', 8, 2)->nullable();
            $table->string('currency', 3)->default('XAF');

            // Status
            $table->enum('status', [
                'pending',
                'accepted',
                'driver_arriving',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->enum('payment_status', [
                'pending',
                'escrowed',
                'completed',
                'refunded',
                'failed'
            ])->default('pending');

            $table->string('payment_method')->nullable();

            // Timestamps
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('payment_released_at')->nullable();

            // Additional
            $table->text('notes')->nullable();
            $table->enum('vehicle_type', ['standard', 'comfort', 'premium'])->default('standard');
            $table->tinyInteger('seats_requested')->default(1);

            $table->timestamps();

            $table->index(['passenger_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
