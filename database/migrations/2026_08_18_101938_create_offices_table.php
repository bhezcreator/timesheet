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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            // Coordonnées GPS du bureau
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Rayon autorisé en mètres
            $table->unsignedInteger('radius')->default(50);

            $table->string('address')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};