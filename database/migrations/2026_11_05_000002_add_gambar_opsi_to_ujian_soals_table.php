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
        Schema::table('ujian_soals', function (Blueprint $table) {
            $table->text('opsi_a')->nullable()->change();
            $table->text('opsi_b')->nullable()->change();
            $table->text('opsi_c')->nullable()->change();
            $table->text('opsi_d')->nullable()->change();

            $table->string('gambar_a')->nullable()->after('opsi_a');
            $table->string('gambar_b')->nullable()->after('opsi_b');
            $table->string('gambar_c')->nullable()->after('opsi_c');
            $table->string('gambar_d')->nullable()->after('opsi_d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_soals', function (Blueprint $table) {
            $table->dropColumn(['gambar_a', 'gambar_b', 'gambar_c', 'gambar_d']);
        });
    }
};
