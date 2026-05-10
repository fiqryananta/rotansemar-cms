<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPenanganan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function opds(): BelongsToMany
    {
        return $this->belongsToMany(Opd::class, 'jenis_penanganan_opd')
            ->withTimestamps()
            ->orderBy('opds.name');
    }

    public function jenisKebutuhans(): BelongsToMany
    {
        return $this->belongsToMany(JenisKebutuhan::class, 'jenis_kebutuhan_jenis_penanganan')
            ->withTimestamps()
            ->orderBy('jenis_kebutuhans.name');
    }

    public function kebutuhans(): HasMany
    {
        return $this->hasMany(PasienKebutuhan::class);
    }
}
