<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // License & ID
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('license_image')->nullable();
            $table->string('id_card_number')->nullable();
            $table->string('id_card_image')->nullable();

            // Vehicle info
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->year('vehicle_year')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('vehicle_plate_number')->nullable();
            $table->string('vehicle_registration_image')->nullable();
            $table->string('vehicle_insurance_image')->nullable();
            $table->enum('vehicle_type', ['standard', 'comfort', 'premium'])->default('standard');
            $table->tinyInteger('seats_available')->default(4);

            // Status & Location
            $table->boolean('is_online')->default(false);
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();

            // KYC
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('kyc_verified_at')->nullable();
            $table->text('kyc_rejected_reason')->nullable();

            // Availability & Stats
            $table->boolean('is_available')->default(false);
            $table->integer('total_rides')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);

            $table->timestamps();

            $table->index(['is_online', 'is_available', 'kyc_status']);
            $table->index(['current_latitude', 'current_longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
