<?php

namespace App\Support;

use App\Models\Pasien;

class MobilePatientTransformer
{
    public static function detail(Pasien $pasien): array
    {
        return [
            'id' => $pasien->id,
            'name' => $pasien->name,
            'nik' => $pasien->nik,
            'birth_date' => $pasien->birth_date,
            'gender' => $pasien->gender,
            'address' => $pasien->address,
            'new_address' => $pasien->new_address,
            'coordinates' => $pasien->coordinates,
            'treatment_start_date' => $pasien->treatment_start_date,
            'visit_date' => $pasien->visit_date,
            'information_date' => $pasien->information_date,
            'treatment_status' => $pasien->treatment_status,
            'catatan_kebutuhan' => $pasien->catatan_kebutuhan,
            'faskes' => $pasien->faskes?->name,
            'puskesmas' => $pasien->puskesmas?->name,
            'kecamatan' => $pasien->kecamatan?->name,
            'kelurahan' => $pasien->kelurahan?->name,
            'kebutuhans' => $pasien->kebutuhans->map(function ($kebutuhan) {
                return [
                    'id' => $kebutuhan->id,
                    'verification_status' => $kebutuhan->verification_status,
                    'need_detail' => $kebutuhan->need_detail,
                    'jenis_kebutuhan' => $kebutuhan->jenisKebutuhan?->name,
                    'opd' => $kebutuhan->opd?->name,
                    'jenis_penanganan' => $kebutuhan->jenisPenanganan?->name,
                    'tindak_lanjuts' => $kebutuhan->tindakLanjuts->map(function ($tindakLanjut) {
                        return [
                            'id' => $tindakLanjut->id,
                            'keterangan' => $tindakLanjut->keterangan,
                            'created_at' => optional($tindakLanjut->created_at)?->toISOString(),
                            'user' => [
                                'id' => $tindakLanjut->user?->id,
                                'name' => $tindakLanjut->user?->name,
                                'email' => $tindakLanjut->user?->email,
                            ],
                            'fotos' => $tindakLanjut->fotos->map(function ($foto) {
                                return [
                                    'id' => $foto->id,
                                    'path' => $foto->path,
                                    'url' => $foto->url,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }
}
