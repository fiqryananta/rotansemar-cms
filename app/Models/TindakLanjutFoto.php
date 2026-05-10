<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TindakLanjutFoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'tindak_lanjut_id',
        'path',
    ];

    protected $appends = ['url'];

    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(PasienKebutuhanTindakLanjut::class, 'tindak_lanjut_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
