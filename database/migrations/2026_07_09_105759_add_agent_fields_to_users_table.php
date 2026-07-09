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
        Schema::table('users', function (Blueprint $table) {

            $table->string('num_order')
                ->unique()
                ->after('id');

            $table->string('first_name')
                ->after('name');

            $table->string('last_name')
                ->after('first_name');

            $table->string('job_title')
                ->after('last_name');

            $table->foreignId('supervisor_id')
                ->nullable()
                ->after('job_title')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('signature')
                ->nullable()
                ->after('supervisor_id');

            $table->string('photo')
                ->nullable()
                ->after('signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['supervisor_id']);

            $table->dropColumn([
                'num_order',
                'first_name',
                'last_name',
                'job_title',
                'supervisor_id',
                'signature',
                'photo',
            ]);
        });
    }
};
