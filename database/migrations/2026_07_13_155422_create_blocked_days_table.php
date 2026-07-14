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
        Schema::create('blocked_days', function (Blueprint $table) {
            $table->id();

            $table->date('date')->unique();

            // Nom du jour (Noël, Nouvel An, Maintenance...)
            $table->string('name');

            $table->enum('type', [
                'Jour férié',
                'Fête religieuse',
                'Congé entreprise',
                'Pont entreprise',
                'Jour chômé',
                'Événement interne',
                'Urgence / Force majeure',
                'Maintenance',
                'Autre',
            ])->default('Jour férié');

            // Permet de désactiver temporairement un jour
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('date');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_days');
    }
};
