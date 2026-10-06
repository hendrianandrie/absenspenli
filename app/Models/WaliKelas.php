<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas';

    protected $fillable = [
        'kelas',
        'user_id',
        'tahun_ajaran',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'kelas', 'kelas');
    }
}
