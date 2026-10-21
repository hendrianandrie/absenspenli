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

    protected static function booted()
    {
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
