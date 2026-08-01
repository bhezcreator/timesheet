<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('monthly_reports', function (Blueprint $table) {
            // 1. Vérifier si l'index existe avant de le supprimer
            $indexes = Schema::getIndexes('monthly_reports');
            $indexExists = collect($indexes)->contains('name', 'monthly_reports_user_id_month_year_unique');

            if ($indexExists) {
                $table->dropUnique('monthly_reports_user_id_month_year_unique');
            }

            // 2. Changer le type de la colonne en string
            $table->string('project_ids', 50)->nullable()->change();

            // 3. Supprimer l'ancien index s'il existe sous un autre nom
            $uniqueExists = collect($indexes)->contains(function ($index) {
                return $index['columns'] === ['user_id', 'month', 'year', 'project_ids'];
            });

            if ($uniqueExists) {
                // Trouver le nom exact et le supprimer
                $indexName = collect($indexes)->firstWhere('columns', ['user_id', 'month', 'year', 'project_ids'])['name'];
                $table->dropUnique($indexName);
            }

            // 4. Ajouter la nouvelle contrainte unique
            $table->unique(['user_id', 'month', 'year', 'project_ids'], 'unique_user_month_project');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('monthly_reports', function (Blueprint $table) {
            // Supprimer la nouvelle contrainte
            $table->dropUnique('unique_user_month_project');

            // Remettre en JSON
            $table->json('project_ids')->nullable()->change();

            // Vérifier si l'ancien index existe
            $indexes = Schema::getIndexes('monthly_reports');
            $indexExists = collect($indexes)->contains('name', 'monthly_reports_user_id_month_year_unique');

            if (! $indexExists) {
                $table->unique(['user_id', 'month', 'year'], 'monthly_reports_user_id_month_year_unique');
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
