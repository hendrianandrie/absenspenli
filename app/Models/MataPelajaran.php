<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'tingkat',
        'kkm',
        'bobot_tugas',
        'bobot_uh',
        'bobot_uts',
        'bobot_uas',
    ];

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'mata_pelajaran_user', 'mata_pelajaran_id', 'user_id')->withTimestamps();
    }

    /**
     * Detect grade level (7, 8, 9, or Semua) from class name.
     */
    public static function getTingkatFromKelas(?string $kelas): string
    {
        $k = trim($kelas ?? '');
        if (preg_match('/^(VIII|8)/i', $k)) return '8';
        if (preg_match('/^(VII|7)/i', $k)) return '7';
        if (preg_match('/^(IX|9)/i', $k)) return '9';
        return 'Semua';
    }

    /**
     * Filter a collection or array of classes according to this subject's tingkat.
     */
    public function filterKelasCollection($kelasCollection)
    {
        $tingkat = $this->tingkat ?? 'Semua';
        if ($tingkat === 'Semua') {
            return collect($kelasCollection)->values();
        }

        $filtered = collect($kelasCollection)->filter(function ($k) use ($tingkat) {
            $t = self::getTingkatFromKelas($k);
            return $t === $tingkat || $t === 'Semua';
        })->values();

        return $filtered->isNotEmpty() ? $filtered : collect($kelasCollection)->values();
    }
}
