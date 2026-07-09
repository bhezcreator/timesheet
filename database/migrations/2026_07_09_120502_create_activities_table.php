<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {

            $table->id();

            /*
             |-----------------------------------------
             | Relations
             |-----------------------------------------
             */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sub_project_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('activity_type_id')
                ->constrained()
                ->restrictOnDelete();

            /*
             |-----------------------------------------
             | Informations
             |-----------------------------------------
             */

            $table->date('activity_date');

            $table->time('start_time')->nullable();

            $table->time('end_time')->nullable();

            $table->decimal('duration', 5, 2);

            $table->text('description');

            /*
             |-----------------------------------------
             | Validation
             |-----------------------------------------
             */

            $table->enum('status', [
                'brouillon',
                'soumis',
                'approuvé',
                'rejeté'
            ])->default('brouillon');

            $table->text('rejection_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
