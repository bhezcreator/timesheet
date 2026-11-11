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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            // Agent
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Bureau
            $table->foreignId('office_id')
                ->constrained()
                ->restrictOnDelete();

            // Type de pointage
            $table->enum('type', [
                'check_in',
                'check_out',
            ]);

            // Date et heure du pointage
            $table->date('attendance_date');
            $table->dateTime('recorded_at');

            // Position GPS de l'agent
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Précision GPS en mètres
            $table->decimal('accuracy', 8, 2)->nullable();

            // Distance entre l'agent et le bureau en mètres
            $table->decimal('distance', 8, 2)->nullable();

            // Token du QR utilisé
            $table->string('qr_token')->nullable();

            // Informations techniques
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Résultat du contrôle
            $table->enum('status', [
                'accepted',
                'rejected',
            ])->default('accepted');

            // Explication en cas de refus
            $table->string('reason')->nullable();

            $table->timestamps();

            // Index utiles
            $table->index([
                'user_id',
                'attendance_date',
            ]);

            $table->index([
                'office_id',
                'attendance_date',
            ]);

            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};