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
        Schema::create('dean_calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dean_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ipcr_submission_id')->constrained('ipcr_submissions')->cascadeOnDelete();
            $table->json('calibration_data')->nullable(); // JSON array of per-row {q, e, t, a, remarks} overrides
            $table->decimal('overall_score', 4, 2)->nullable();
            $table->string('status', 20)->default('draft'); // draft | calibrated
            $table->timestamps();

            $table->unique(['dean_id', 'ipcr_submission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dean_calibrations');
    }
};
