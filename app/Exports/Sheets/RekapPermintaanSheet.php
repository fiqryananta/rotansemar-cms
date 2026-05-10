<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapPermintaanSheet implements FromArray, WithTitle
{
    public function __construct(
        private string $dari,
        private string $sampai,
    ) {}

    public function title(): string
    {
        return 'Rekap Permintaan';
    }

    public function array(): array
    {
        $dariLabel   = Carbon::parse($this->dari)->format('d-m-Y');
        $sampaiLabel = Carbon::parse($this->sampai)->format('d-m-Y');
        $dariTs      = $this->dari . ' 00:00:00';
        $sampaiTs    = $this->sampai . ' 23:59:59';

        $rows = DB::table('jenis_kebutuhans')
            ->leftJoin('pasien_kebutuhans', function ($join) use ($dariTs, $sampaiTs) {
                $join->on('pasien_kebutuhans.jenis_kebutuhan_id', '=', 'jenis_kebutuhans.id')
                    ->where('pasien_kebutuhans.created_at', '>=', $dariTs)
                    ->where('pasien_kebutuhans.created_at', '<=', $sampaiTs);
            })
            ->select([
                'jenis_kebutuhans.name',
                DB::raw('COUNT(pasien_kebutuhans.id) as total'),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status IS NULL THEN 1 ELSE 0 END) as belum"),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status = 'tidak_layak' THEN 1 ELSE 0 END) as tidak_bisa"),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status IN ('proses','pending_bantuan') THEN 1 ELSE 0 END) as proses"),
                DB::raw("SUM(CASE WHEN pasien_kebutuhans.verification_status = 'selesai' THEN 1 ELSE 0 END) as selesai"),
            ])
            ->groupBy('jenis_kebutuhans.id', 'jenis_kebutuhans.name')
            ->orderBy('jenis_kebutuhans.name')
            ->get();

        $data = [
            [null, 'Tanggal', $dariLabel, 's/d', $sampaiLabel, null, null],
            [null, null, null, null, null, null, null],
            [null, 'Jenis Kebutuhan', 'Total permintaan bantuan', 'Belum di tindak lanjuti', 'Tidak bisa di intervensi', 'Proses', 'Selesai'],
        ];

        $tTotal = $tBelum = $tTidakBisa = $tProses = $tSelesai = 0;

        foreach ($rows as $row) {
            $data[] = [
                null,
                $row->name,
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

        $data[] = [null, 'Total', $tTotal, $tBelum, $tTidakBisa, $tProses, $tSelesai];

        return $data;
    }
}
