<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Puskesmas extends Model
{
    use HasFactory;

    protected $table = 'puskesmas';

    protected $fillable = [
        'name',
    ];

    public function kelurahans(): BelongsToMany
    {
        return $this->belongsToMany(Kelurahan::class, 'kelurahan_puskesmas')
            ->withTimestamps()
            ->orderBy('kelurahans.name');
    }
}
