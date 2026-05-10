<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapKelurahanSheet implements FromArray, WithTitle
{
    public function __construct(
        private string $dari,
        private string $sampai,
    ) {}

    public function title(): string
    {
        return 'Rekap Kelurahan';
    }

    public function array(): array
    {
        $dariLabel   = Carbon::parse($this->dari)->format('d-m-Y');
        $sampaiLabel = Carbon::parse($this->sampai)->format('d-m-Y');
        $dariTs      = $this->dari . ' 00:00:00';
        $sampaiTs    = $this->sampai . ' 23:59:59';

        $rows = DB::table('kelurahan_puskesmas as kp')
            ->join('kelurahans', 'kelurahans.id', '=', 'kp.kelurahan_id')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->join('puskesmas', 'puskesmas.id', '=', 'kp.puskesmas_id')
            ->leftJoin('pasiens', function ($join) {
                $join->on('pasiens.kelurahan_id', '=', 'kp.kelurahan_id')
                    ->on('pasiens.puskesmas_id', '=', 'kp.puskesmas_id');
            })
            ->leftJoin('pasien_kebutuhans', function ($join) use ($dariTs, $sampaiTs) {
                $join->on('pasien_kebutuhans.pasien_id', '=', 'pasiens.id')
                    ->where('pasien_kebutuhans.created_at', '>=', $dariTs)
                    ->where('pasien_kebutuhans.created_at', '<=', $sampaiTs);
            })
            ->select([
                'kecamatans.name as kecamatan',
                'kelurahans.name as kelurahan',
                'puskesmas.name as puskesmas',
                DB::raw('COUNT(pasien_kebutuhans.id) as total'),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status IS NULL THEN 1 ELSE 0 END) as belum"),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status = 'tidak_layak' THEN 1 ELSE 0 END) as tidak_bisa"),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status IN ('proses','pending_bantuan') THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status = 'selesai' THEN 1 ELSE 0 END) as selesai"),
            ])
            ->groupBy('kp.kelurahan_id', 'kp.puskesmas_id', 'kecamatans.name', 'kelurahans.name', 'puskesmas.name')
            ->orderBy('kecamatans.name')
            ->orderBy('kelurahans.name')
            ->orderBy('puskesmas.name')
            ->get();

        $data = [
            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],
            [null, 'Tanggal', $dariLabel, 's/d', $sampaiLabel, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],
            [null, 'Kecamatan', 'Kelurahan', 'Puskesmas', 'Total permintaan bantuan', 'Belum di tindak lanjuti', 'Tidak bisa di intervensi', 'Proses', 'Selesai'],
        ];

        $tTotal = $tBelum = $tTidakBisa = $tProses = $tSelesai = 0;

        foreach ($rows as $row) {
            $data[] = [
                null,
                $row->kecamatan,
                $row->kelurahan,
                $row->puskesmas,
                (int) $row->total,
                (int) $row->belum,
                (int) $row->tidak_bisa,
                (int) $row->proses,
                (int) $row->selesai,
            ];
            $tTotal    += (int) $row->total;
            $tBelum    += (int) $row->belum;
            $tTidakBisa += (int) $row->tidak_bisa;
            $tProses   += (int) $row->proses;
            $tSelesai  += (int) $row->selesai;
        }

        $data[] = [null, 'Total', null, null, $tTotal, $tBelum, $tTidakBisa, $tProses, $tSelesai];

        return $data;
    }
}
