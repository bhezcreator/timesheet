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
        // Vérifier si la colonne existe avant de faire quoi que ce soit
        if (Schema::hasColumn('activities', 'monthly_report_id')) {

            // 1. Supprimer la clé étrangère (ignore les erreurs)
            try {
                Schema::table('activities', function (Blueprint $table) {
                    $table->dropForeign(['monthly_report_id']);
                });
            } catch (\Exception $e) {
                // Ignorer l'erreur - la clé étrangère n'existe probablement pas
            }

            // 2. Supprimer l'index (ignore les erreurs)
            try {
                Schema::table('activities', function (Blueprint $table) {
                    $table->dropIndex(['monthly_report_id']);
                });
            } catch (\Exception $e) {
                // Ignorer l'erreur - l'index n'existe probablement pas
            }

            // 3. Supprimer la colonne (c'est la seule opération critique)
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('monthly_report_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('activities', 'monthly_report_id')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->foreignId('monthly_report_id')
                    ->nullable()
                    ->constrained('monthly_reports')
                    ->onDelete('set null');

                $table->index('monthly_report_id');
            });
        }
    }
};
