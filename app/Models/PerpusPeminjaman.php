<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerpusPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'perpus_peminjamans';

    protected $fillable = [
        'kode_transaksi',
        'siswa_id',
        'buku_id',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
        'denda',
        'petugas_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_kembali' => 'date',
        'denda' => 'decimal:2',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(PerpusBuku::class, 'buku_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function resume(): HasOne
    {
        return $this->hasOne(PerpusResume::class, 'peminjaman_id');
    }

    public function getIsTerlambatAttribute(): bool
    {
        if ($this->status === 'kembali') {
            return $this->tanggal_kembali && $this->tanggal_kembali->gt($this->tanggal_jatuh_tempo);
        }
        return Carbon::today()->gt($this->tanggal_jatuh_tempo);
    }

    public function getHariTerlambatAttribute(): int
    {
        if ($this->status === 'kembali') {
            return ($this->tanggal_kembali && $this->tanggal_kembali->gt($this->tanggal_jatuh_tempo))
                ? $this->tanggal_jatuh_tempo->diffInDays($this->tanggal_kembali)
                : 0;
        }
        return Carbon::today()->gt($this->tanggal_jatuh_tempo)
            ? $this->tanggal_jatuh_tempo->diffInDays(Carbon::today())
            : 0;
    }

    public static function generateKodeTransaksi(): string
    {
        $prefix = 'PJM-' . date('Ym') . '-';
        $last = self::where('kode_transaksi', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $num = (int) substr($last->kode_transaksi, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
