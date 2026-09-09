<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            if (! Schema::hasColumn('mata_pelajarans', 'tingkat')) {
                $table->string('tingkat', 20)->default('Semua')->after('nama_mapel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            if (Schema::hasColumn('mata_pelajarans', 'tingkat')) {
                $table->dropColumn('tingkat');
            }
        });
    }
};
