<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'mata_pelajaran_id',
        'kelas',
        'nama_kegiatan',
        'jenis',
        'semester',
        'tahun_ajaran',
        'tanggal',
    ];

    protected static $schemaChecked = false;

    protected static function booted()
    {
        if (!static::$schemaChecked) {
            static::$schemaChecked = true;
            try {
                if (!\Illuminate\Support\Facades\Cache::get('schema_kegiatans_has_semester')) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kegiatans', 'semester')) {
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `kegiatans` ADD `semester` TINYINT NOT NULL DEFAULT 1 AFTER `jenis`, ADD `tahun_ajaran` VARCHAR(20) NOT NULL DEFAULT '2026/2027' AFTER `semester`");
                        \Illuminate\Support\Facades\DB::statement("UPDATE kegiatans SET semester = CASE WHEN MONTH(tanggal) >= 7 AND MONTH(tanggal) <= 12 THEN 1 ELSE 2 END");
                    }
                    \Illuminate\Support\Facades\Cache::forever('schema_kegiatans_has_semester', true);
                }
            } catch (\Throwable $e) {
                // Silently catch to avoid breaking if user has no ALTER permissions
            }
        }

        static::creating(function ($kegiatan) {
            if (empty($kegiatan->semester)) {
                $kegiatan->semester = self::determineSemester($kegiatan->tanggal ?? now());
            }
            if (empty($kegiatan->tahun_ajaran)) {
                $kegiatan->tahun_ajaran = self::determineTahunAjaran($kegiatan->tanggal ?? now());
            }
        });
    }

    public static function determineSemester($date = null): int
    {
        $m = (int) \Carbon\Carbon::parse($date ?? now())->month;
        return ($m >= 7 && $m <= 12) ? 1 : 2;
    }

    public static function determineTahunAjaran($date = null): string
    {
        $carbon = \Carbon\Carbon::parse($date ?? now());
        $y = (int) $carbon->year;
        $m = (int) $carbon->month;
        return ($m >= 7) ? "{$y}/" . ($y + 1) : ($y - 1) . "/{$y}";
    }

    public static function currentSemester(): int
    {
        return self::determineSemester();
    }

    public static function currentTahunAjaran(): string
    {
        return self::determineTahunAjaran();
    }

    public function getSemesterLabelAttribute(): string
    {
        return $this->semester == 1 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)';
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
}
