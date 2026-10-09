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
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'foto_wajah')) {
                $table->string('foto_wajah')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('siswas', 'face_descriptor')) {
                $table->longText('face_descriptor')->nullable()->after('foto_wajah');
            }
            if (!Schema::hasColumn('siswas', 'face_enrolled_at')) {
                $table->timestamp('face_enrolled_at')->nullable()->after('face_descriptor');
            }
        });

        Schema::table('absensis', function (Blueprint $table) {
            if (!Schema::hasColumn('absensis', 'metode')) {
                $table->string('metode', 20)->default('manual')->after('status');
            }
            if (!Schema::hasColumn('absensis', 'foto_scan')) {
                $table->string('foto_scan')->nullable()->after('metode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['foto_wajah', 'face_descriptor', 'face_enrolled_at']);
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn(['metode', 'foto_scan']);
        });
    }
};
