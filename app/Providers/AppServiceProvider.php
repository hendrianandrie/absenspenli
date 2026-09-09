<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('mata_pelajarans')) {
                if (! \Illuminate\Support\Facades\Schema::hasColumn('mata_pelajarans', 'tingkat')) {
                    \Illuminate\Support\Facades\Schema::table('mata_pelajarans', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('tingkat', 20)->default('Semua')->after('nama_mapel');
                    });
                }
                if (! \Illuminate\Support\Facades\Schema::hasColumn('mata_pelajarans', 'bobot_tugas')) {
                    \Illuminate\Support\Facades\Schema::table('mata_pelajarans', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->integer('bobot_tugas')->default(20)->after('kkm');
                        $table->integer('bobot_uh')->default(30)->after('bobot_tugas');
                        $table->integer('bobot_uts')->default(25)->after('bobot_uh');
                        $table->integer('bobot_uas')->default(25)->after('bobot_uts');
                    });
                }
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'mata_pelajaran_id')) {
                    \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajarans')->nullOnDelete();
                    });
                }
                if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'kelas_diampu')) {
                    \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->json('kelas_diampu')->nullable();
                    });
                }
            }
        } catch (\Throwable $e) {
            // Ignore schema exception if DB is not connected during boot
        }
    }
}
