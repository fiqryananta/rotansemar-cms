<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisKebutuhan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function jenisPenanganans(): BelongsToMany
    {
        return $this->belongsToMany(JenisPenanganan::class, 'jenis_kebutuhan_jenis_penanganan')
            ->withTimestamps()
            ->orderBy('jenis_penanganans.name');
    }

    public function pasienKebutuhans(): HasMany
    {
        return $this->hasMany(PasienKebutuhan::class);
    }

    public function pasiens(): BelongsToMany
    {
        return $this->belongsToMany(Pasien::class, 'pasien_jenis_kebutuhan')
            ->withTimestamps();
    }
}
