<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dean_calibrations', function (Blueprint $table) {
            $table->text('dean_comment')->nullable()->after('status');
            $table->text('dean_suggestion')->nullable()->after('dean_comment');
        });
    }

    public function down(): void
    {
        Schema::table('dean_calibrations', function (Blueprint $table) {
            $table->dropColumn(['dean_comment', 'dean_suggestion']);
        });
    }
};
