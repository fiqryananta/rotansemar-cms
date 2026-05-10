<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PasienKebutuhan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pasien_id',
        'jenis_kebutuhan_id',
        'opd_id',
        'jenis_penanganan_id',
        'need_detail',
        'verification_status',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function jenisKebutuhan(): BelongsTo
    {
        return $this->belongsTo(JenisKebutuhan::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function jenisPenanganan(): BelongsTo
    {
        return $this->belongsTo(JenisPenanganan::class);
    }

    public function tindakLanjuts(): HasMany
    {
        return $this->hasMany(PasienKebutuhanTindakLanjut::class, 'pasien_kebutuhan_id')->latest();
    }
}
