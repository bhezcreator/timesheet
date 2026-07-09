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
        Schema::create('report_validations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('monthly_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('validator_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('decision', [
                'approuvé',
                'rejeté'
            ]);

            $table->text('comment')->nullable();
            $table->timestamp('validated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_validations');
    }
};
