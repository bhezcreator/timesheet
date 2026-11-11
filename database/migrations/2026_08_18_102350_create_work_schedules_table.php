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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();

            // Bureau concerné
            $table->foreignId('office_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            // 1 = lundi ... 7 = dimanche
            $table->unsignedTinyInteger('day_of_week');

            // Horaires
            $table->time('start_time');
            $table->time('end_time');

            // Tolérance avant de considérer l'agent comme en retard
            $table->unsignedSmallInteger('grace_period')->default(15);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

/*             $table->unique([
                'office_id',
                'day_of_week',
            ]); */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};