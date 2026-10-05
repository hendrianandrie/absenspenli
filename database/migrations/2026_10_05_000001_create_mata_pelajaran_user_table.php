<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mata_pelajaran_user')) {
            Schema::create('mata_pelajaran_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'mata_pelajaran_id']);
            });
        }

        // Migrate existing teachers with mata_pelajaran_id
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'mata_pelajaran_id')) {
            $existing = DB::table('users')->whereNotNull('mata_pelajaran_id')->get();
            foreach ($existing as $u) {
                DB::table('mata_pelajaran_user')->updateOrInsert(
                    ['user_id' => $u->id, 'mata_pelajaran_id' => $u->mata_pelajaran_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_pelajaran_user');
    }
};
