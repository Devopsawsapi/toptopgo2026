<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rendre user_id nullable (trajet créé par chauffeur sans client encore)
        DB::statement("ALTER TABLE trips MODIFY COLUMN user_id BIGINT UNSIGNED NULL");

        // Rendre les coordonnées nullable
        DB::statement("ALTER TABLE trips MODIFY COLUMN pickup_lat DECIMAL(10,7) NULL");
        DB::statement("ALTER TABLE trips MODIFY COLUMN pickup_lng DECIMAL(10,7) NULL");
        DB::statement("ALTER TABLE trips MODIFY COLUMN dropoff_lat DECIMAL(10,7) NULL");
        DB::statement("ALTER TABLE trips MODIFY COLUMN dropoff_lng DECIMAL(10,7) NULL");

        // Rendre amount nullable avec défaut 0
        DB::statement("ALTER TABLE trips MODIFY COLUMN amount DECIMAL(10,2) NULL DEFAULT 0");

        // Ajouter les colonnes manquantes pour les trajets chauffeur
        DB::statement("ALTER TABLE trips ADD COLUMN IF NOT EXISTS price_per_seat DECIMAL(10,2) NULL DEFAULT 0");
        DB::statement("ALTER TABLE trips ADD COLUMN IF NOT EXISTS available_seats INT NULL DEFAULT 1");
        DB::statement("ALTER TABLE trips ADD COLUMN IF NOT EXISTS departure_date DATE NULL");
        DB::statement("ALTER TABLE trips ADD COLUMN IF NOT EXISTS departure_time VARCHAR(10) NULL");
        DB::statement("ALTER TABLE trips ADD COLUMN IF NOT EXISTS luggage_included INT NULL DEFAULT 1");
        DB::statement("ALTER TABLE trips ADD COLUMN IF NOT EXISTS extra_luggage_fee DECIMAL(10,2) NULL DEFAULT 0");
    }

    public function down(): void {}
};