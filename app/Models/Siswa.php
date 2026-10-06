<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nama',
        'kelas',
        'jenis_kelamin',
    ];

    protected static function booted()
    {
        static::created(function (Siswa $siswa) {
            if (!empty($siswa->nis)) {
                User::updateOrCreate(
                    ['siswa_id' => $siswa->id],
                    [
                        'name' => $siswa->nama,
                        'username' => $siswa->nis,
                        'email' => $siswa->nis . '@siswa.smpn5ciamis.sch.id',
                        'password' => \Illuminate\Support\Facades\Hash::make($siswa->nis),
                        'role' => 'siswa',
                    ]
                );
            }
        });

        static::updated(function (Siswa $siswa) {
            if (!empty($siswa->nis)) {
                $user = User::where('siswa_id', $siswa->id)->first();
                if ($user) {
                    $user->update([
                        'name' => $siswa->nama,
                        'username' => $siswa->nis,
                        'password' => \Illuminate\Support\Facades\Hash::make($siswa->nis),
                    ]);
                }
            }
        });

        static::deleted(function (Siswa $siswa) {
            User::where('siswa_id', $siswa->id)->delete();
        });
    }

    public function user()
    {
        return $this->hasOne(User::class, 'siswa_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
}
