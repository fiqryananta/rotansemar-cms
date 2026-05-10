<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PasienKebutuhanTindakLanjut extends Model
{
    use HasFactory;

    protected $fillable = [
        'pasien_kebutuhan_id',
        'user_id',
        'keterangan',
    ];

    public function pasienKebutuhan(): BelongsTo
    {
        return $this->belongsTo(PasienKebutuhan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(TindakLanjutFoto::class, 'tindak_lanjut_id');
    }
}
