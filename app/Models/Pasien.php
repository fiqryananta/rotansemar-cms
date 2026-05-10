<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasiens';

    protected $fillable = [
        'name',
        'nik',
        'birth_date',
        'gender',
        'faskes_id',
        'puskesmas_id',
        'kecamatan_id',
        'kelurahan_id',
        'address',
        'coordinates',
        'new_address',
        'weight',
        'height',
        'ever_received_assistance',
        'willing_to_help',
        'economic_status',
        'pekerjaan_id',
        'workplace_name',
        'workplace_address',
        'family_head_name',
        'family_head_nik',
        'family_head_pekerjaan_id',
        'parent_marital_status',
        'parenting_pattern',
        'family_income_range',
        'respondent',
        'patient_relationship',
        'tb_so_ro',
        'treatment_start_date',
        'visit_date',
        'information_date',
        'treatment_status',
        'transmission_source',
        'follow_up_plan',
        'pregnancy_status',
        'comorbid_status',
        'smoking_behavior',
        'family_smoking_status',
        'immunization_status',
        'nutritional_status',
        'jkn_ownership',
        'home_area',
        'house_area',
        'house_type',
        'house_status',
        'home_lighting',
        'home_humidity',
        'home_cleanliness',
        'home_floor',
        'home_ventilation',
        'home_ceiling',
        'home_ceiling_condition',
        'home_wall',
        'home_bedroom_window',
        'home_family_room_window',
        'home_kitchen_smoke_hole',
        'home_open_family_room_window',
        'home_clean_house_habit',
        'sanitation_clean_water',
        'sanitation_toilet',
        'sanitation_wastewater_disposal',
        'sanitation_garbage_water_disposal',
        'sanitation_trash',
        'sanitation_feces_disposal',
        'sanitation_throw_trash_habit',
        'sanitation_handwashing_habit',
        'has_livestock',
        'has_animal_cage',
        'catatan_kebutuhan',
    ];

    protected $casts = [
        'ever_received_assistance' => 'boolean',
        'willing_to_help' => 'boolean',
        'respondent' => 'boolean',
        'pregnancy_status' => 'boolean',
        'comorbid_status' => 'boolean',
        'smoking_behavior' => 'boolean',
        'family_smoking_status' => 'boolean',
        'jkn_ownership' => 'boolean',
        'has_livestock' => 'boolean',
        'has_animal_cage' => 'boolean',
    ];

    public function faskes(): BelongsTo
    {
        return $this->belongsTo(Faskes::class);
    }

    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    public function pekerjaan(): BelongsTo
    {
        return $this->belongsTo(Pekerjaan::class);
    }

    public function familyHeadPekerjaan(): BelongsTo
    {
        return $this->belongsTo(Pekerjaan::class, 'family_head_pekerjaan_id');
    }

    public function kebutuhans(): HasMany
    {
        return $this->hasMany(PasienKebutuhan::class);
    }

    public function jenisKebutuhans(): BelongsToMany
    {
        return $this->belongsToMany(JenisKebutuhan::class, 'pasien_jenis_kebutuhan')
            ->withTimestamps()
            ->orderBy('jenis_kebutuhans.name');
    }

    public function medicationPickups(): HasMany
    {
        return $this->hasMany(MedicationPickup::class);
    }

    public function visitResults(): HasMany
    {
        return $this->hasMany(VisitResult::class);
    }
}
