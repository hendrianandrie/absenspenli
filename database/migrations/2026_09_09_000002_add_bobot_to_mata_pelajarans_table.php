<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            if (! Schema::hasColumn('mata_pelajarans', 'bobot_tugas')) {
                $table->integer('bobot_tugas')->default(20)->after('kkm');
                $table->integer('bobot_uh')->default(30)->after('bobot_tugas');
                $table->integer('bobot_uts')->default(25)->after('bobot_uh');
                $table->integer('bobot_uas')->default(25)->after('bobot_uts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropColumn(['bobot_tugas', 'bobot_uh', 'bobot_uts', 'bobot_uas']);
        });
    }
};
