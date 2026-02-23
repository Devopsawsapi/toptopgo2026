<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->onDelete('cascade');
            $table->foreignId('rater_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('rating', 2, 1); // 1.0 to 5.0
            $table->text('comment')->nullable();
            $table->enum('type', ['passenger_to_driver', 'driver_to_passenger']);
            $table->timestamps();

            $table->unique(['ride_id', 'rater_user_id', 'type']);
            $table->index(['rated_user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
