<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_report_id')
                ->constrained('monthly_reports')
                ->onDelete('cascade');
            $table->foreignId('activity_id')
                ->constrained('activities')
                ->onDelete('cascade');
            $table->string('status')->default('brouillon');
            $table->timestamps();

            // Empêcher les doublons
            $table->unique(['monthly_report_id', 'activity_id'], 'report_activity_unique');

            // Index pour les performances
            $table->index(['monthly_report_id', 'status']);
            $table->index('activity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_activity');
    }
};
