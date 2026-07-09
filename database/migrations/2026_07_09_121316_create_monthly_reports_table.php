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
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->year('year');
            $table->date('report_date');
            $table->longText('objectives');
            $table->longText('achievements');
            $table->longText('next_actions')->nullable();

            $table->enum('status', [
                'brouillon',
                'soumis',
                'approuvé',
                'rejeté'
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'month',
                'year'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
