<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerpusPoinLog extends Model
{
    use HasFactory;

    protected $table = 'perpus_poin_logs';

    protected $fillable = [
        'siswa_id',
        'jenis_aktivitas',
        'poin',
        'referensi_id',
        'keterangan',
        'bulan',
        'tahun',
    ];

    protected $casts = [
        'poin' => 'integer',
        'bulan' => 'integer',
        'tahun' => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public static function catatPoin(int $siswaId, string $jenis, int $poin, ?int $referensiId = null, ?string $keterangan = null): self
    {
        return self::create([
            'siswa_id' => $siswaId,
            'jenis_aktivitas' => $jenis,
            'poin' => $poin,
            'referensi_id' => $referensiId,
            'keterangan' => $keterangan,
            'bulan' => (int) date('n'),
            'tahun' => (int) date('Y'),
        ]);
    }
}
