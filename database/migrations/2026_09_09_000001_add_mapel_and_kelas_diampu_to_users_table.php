<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mata_pelajaran_id')) {
                $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajarans')->nullOnDelete()->after('role');
            }
            if (! Schema::hasColumn('users', 'kelas_diampu')) {
                $table->text('kelas_diampu')->nullable()->after('mata_pelajaran_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mata_pelajaran_id')) {
                $table->dropForeign(['mata_pelajaran_id']);
                $table->dropColumn('mata_pelajaran_id');
            }
            if (Schema::hasColumn('users', 'kelas_diampu')) {
                $table->dropColumn('kelas_diampu');
            }
        });
    }
};
