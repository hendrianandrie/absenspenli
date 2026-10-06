<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'nip',
        'email',
        'password',
        'role',
        'mata_pelajaran_id',
        'kelas_diampu',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'kelas_diampu' => 'array',
        ];
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function mataPelajarans()
    {
        return $this->belongsToMany(MataPelajaran::class, 'mata_pelajaran_user', 'user_id', 'mata_pelajaran_id')->withTimestamps();
    }

    /**
     * Get all assigned mapel IDs (from pivot table or fallback to mata_pelajaran_id)
     */
    public function getAssignedMapelIdsAttribute(): array
    {
        if ($this->relationLoaded('mataPelajarans')) {
            $ids = $this->mataPelajarans->pluck('id')->toArray();
        } else {
            $ids = $this->mataPelajarans()->pluck('mata_pelajarans.id')->toArray();
        }

        if (empty($ids) && $this->mata_pelajaran_id) {
            $ids = [$this->mata_pelajaran_id];
        }

        return $ids;
    }

    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class, 'user_id');
    }

    public function waliKelases()
    {
        return $this->hasMany(WaliKelas::class, 'user_id');
    }

    public function getIsWaliKelasAttribute(): bool
    {
        return $this->waliKelas()->exists();
    }
}
