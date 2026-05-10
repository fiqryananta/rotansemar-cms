<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanPenyuluhan extends Model
{
    protected $fillable = [
        'nama_kegiatan',
        'tanggal_kegiatan',
        'uraian_kegiatan',
        'lokasi_kegiatan',
        'koordinat_lokasi',
        'sasaran',
        'jumlah_sasaran',
        'foto_kegiatan',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
        'jumlah_sasaran' => 'integer',
        'foto_kegiatan' => 'array',
    ];
}
