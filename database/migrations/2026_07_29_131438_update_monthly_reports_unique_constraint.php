<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Désactiver complètement la vérification au niveau de la connexion
        Schema::disableForeignKeyConstraints();

        // 2. Récupérer les index avant l'opération
        $indexes = Schema::getIndexes('monthly_reports');
        $indexExists = collect($indexes)->contains('name', 'monthly_reports_user_id_month_year_unique');

        // On isole la suppression de la clé étrangère SI elle existe et bloque l'index
        Schema::table('monthly_reports', function (Blueprint $table) use ($indexExists) {
            if ($indexExists) {
                // Étape clé : Supprimer la clé étrangère sur user_id d'abord
                $table->dropForeign(['user_id']);
                $table->dropUnique('monthly_reports_user_id_month_year_unique');
            }
        });

        // 3. Appliquer les autres modifications de structure
        Schema::table('monthly_reports', function (Blueprint $table) use ($indexes) {
            $table->string('project_ids', 50)->nullable()->change();

            $uniqueExists = collect($indexes)->contains(function ($index) {
                return $index['columns'] === ['user_id', 'month', 'year', 'project_ids'];
            });

            if ($uniqueExists) {
                $indexName = collect($indexes)->firstWhere('columns', ['user_id', 'month', 'year', 'project_ids'])['name'];
                $table->dropUnique($indexName);
            }

            $table->unique(['user_id', 'month', 'year', 'project_ids'], 'unique_user_month_project');
            
            // Re-créer proprement la contrainte de clé étrangère qui avait été supprimée
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->dropUnique('unique_user_month_project');
            $table->json('project_ids')->nullable()->change();

            $indexes = Schema::getIndexes('monthly_reports');
            $indexExists = collect($indexes)->contains('name', 'monthly_reports_user_id_month_year_unique');

            if (! $indexExists) {
                $table->unique(['user_id', 'month', 'year'], 'monthly_reports_user_id_month_year_unique');
            }
        });

        Schema::enableForeignKeyConstraints();
    }
};
