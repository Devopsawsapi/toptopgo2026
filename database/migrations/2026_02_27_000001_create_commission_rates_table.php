<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des taux de commission par pays et/ou ville.
     * 
     * Règle de priorité :
     *   1. Taux ville (city_id non null)   → priorité haute
     *   2. Taux pays (country_id non null) → priorité basse
     */
    public function up(): void
    {
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->decimal('rate', 5, 2)->comment('Taux en pourcentage. Ex: 15.00 = 15%');
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable()->comment('Description optionnelle du taux');
            $table->timestamps();

            // Un seul taux actif par combinaison pays/ville
            $table->unique(['country_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};