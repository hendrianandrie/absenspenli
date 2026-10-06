<?php

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'siswa_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('siswa_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('siswas')
                    ->nullOnDelete();
            });
        }

        // Generate akun user untuk seluruh data siswa yang sudah ada di database
        if (Schema::hasTable('siswas')) {
            foreach (Siswa::all() as $siswa) {
                if (empty($siswa->nis)) {
                    continue;
                }

                $existingUser = User::where('siswa_id', $siswa->id)
                    ->orWhere('username', $siswa->nis)
                    ->first();

                $emailCandidate = $siswa->nis . '@siswa.smpn5ciamis.sch.id';

                if (!$existingUser) {
                    User::create([
                        'siswa_id' => $siswa->id,
                        'name' => $siswa->nama,
                        'username' => $siswa->nis,
                        'email' => $emailCandidate,
                        'password' => Hash::make($siswa->nis),
                        'role' => 'siswa',
                    ]);
                } else {
                    $existingUser->update([
                        'siswa_id' => $siswa->id,
                        'role' => 'siswa',
                        'name' => $siswa->nama,
                        'password' => Hash::make($siswa->nis), // selalu sinkron dengan NIS
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'siswa_id')) {
            // Hapus akun dengan role siswa yang terbuat
            User::where('role', 'siswa')->delete();

            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['siswa_id']);
                $table->dropColumn('siswa_id');
            });
        }
    }
};
