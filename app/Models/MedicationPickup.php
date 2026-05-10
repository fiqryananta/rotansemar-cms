<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationPickup extends Model
{
    use HasFactory;

    protected $table = 'medication_pickups';

    protected $fillable = [
        'pasien_id',
        'faskes_id',
        'puskesmas_id',
        'scheduled_date',
        'status',
        'actual_date',
        'transfer_date',
        'next_pickup_date',
        'death_date',
        'target_faskes_id',
        'is_outside_city',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'actual_date' => 'date',
        'transfer_date' => 'date',
        'next_pickup_date' => 'date',
        'death_date' => 'date',
        'is_outside_city' => 'boolean',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function faskes(): BelongsTo
    {
        return $this->belongsTo(Faskes::class);
    }

    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class);
    }

    public function targetFaskes(): BelongsTo
    {
        return $this->belongsTo(Faskes::class, 'target_faskes_id');
    }
}
