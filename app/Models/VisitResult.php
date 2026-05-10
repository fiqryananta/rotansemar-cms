<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitResult extends Model
{
    use HasFactory;

    protected $table = 'visit_results';

    protected $fillable = [
        'pasien_id',
        'faskes_id',
        'visit_type',
        'visit_date',
        'findings',
        'follow_up',
        'location_detail',
        'latitude',
        'longitude',
        'location_source',
        'user_id',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function faskes(): BelongsTo
    {
        return $this->belongsTo(Faskes::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
