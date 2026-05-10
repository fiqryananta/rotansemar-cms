<?php

namespace App\Exports\Sheets;

use App\Models\JenisKebutuhan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapKelurahanByPermintaanSheet implements FromArray, WithTitle
{
    public function __construct(
        private string $dari,
        private string $sampai,
    ) {}

    public function title(): string
    {
        return 'Rekap Kelurahan by permintaan';
    }

    public function array(): array
    {
        $dariLabel   = Carbon::parse($this->dari)->format('d-m-Y');
        $sampaiLabel = Carbon::parse($this->sampai)->format('d-m-Y');
        $dariTs      = $this->dari . ' 00:00:00';
        $sampaiTs    = $this->sampai . ' 23:59:59';

        // All jenis kebutuhan (dynamic columns)
        $jenisKebutuhans = JenisKebutuhan::orderBy('name')->get();

        // All kelurahan-puskesmas combinations
        $combinations = DB::table('kelurahan_puskesmas as kp')
            ->join('kelurahans', 'kelurahans.id', '=', 'kp.kelurahan_id')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->join('puskesmas', 'puskesmas.id', '=', 'kp.puskesmas_id')
            ->select([
                'kp.kelurahan_id',
                'kp.puskesmas_id',
                'kecamatans.name as kecamatan',
                'kelurahans.name as kelurahan',
                'puskesmas.name as puskesmas',
            ])
            ->orderBy('kecamatans.name')
            ->orderBy('kelurahans.name')
            ->orderBy('puskesmas.name')
            ->get();

        // Fetch all PasienKebutuhan in range with their kelurahan/puskesmas/jenis
        $kebutuhans = DB::table('pasien_kebutuhans as pk')
            ->join('pasiens', 'pasiens.id', '=', 'pk.pasien_id')
            ->whereBetween('pk.created_at', [$dariTs, $sampaiTs])
            ->select('pasiens.kelurahan_id', 'pasiens.puskesmas_id', 'pk.jenis_kebutuhan_id')
            ->get();

        // Build pivot: "kelurahan_id|puskesmas_id" => [jenis_kebutuhan_id => count]
        $pivot = [];
        foreach ($kebutuhans as $k) {
            $key = $k->kelurahan_id . '|' . $k->puskesmas_id;
            $pivot[$key][$k->jenis_kebutuhan_id] = ($pivot[$key][$k->jenis_kebutuhan_id] ?? 0) + 1;
        }

        // Build column widths: 4 fixed + one per jenis_kebutuhan
        $headerRow = [null, 'Kecamatan', 'Kelurahan', 'Puskesmas'];
        foreach ($jenisKebutuhans as $jk) {
            $headerRow[] = $jk->name;
        }

        $colCount = count($headerRow);
        $emptyRow = array_fill(0, $colCount, null);

        $dateRow = array_fill(0, $colCount, null);
        $dateRow[1] = 'Tanggal';
        $dateRow[2] = $dariLabel;
        $dateRow[3] = 's/d';
        $dateRow[4] = $sampaiLabel;

        $data = [
            $emptyRow,
            $emptyRow,
            $dateRow,
            $emptyRow,
            $headerRow,
        ];

        // Totals per jenis_kebutuhan
        $totals = array_fill(0, count($jenisKebutuhans), 0);

        foreach ($combinations as $combo) {
            $key  = $combo->kelurahan_id . '|' . $combo->puskesmas_id;
            $row  = [null, $combo->kecamatan, $combo->kelurahan, $combo->puskesmas];
            foreach ($jenisKebutuhans as $i => $jk) {
                $count   = $pivot[$key][$jk->id] ?? 0;
                $row[]   = $count;
                $totals[$i] += $count;
            }
            $data[] = $row;
        }

        // Total row
        $totalRow = [null, 'Total', null, null];
        foreach ($totals as $t) {
            $totalRow[] = $t;
        }
        $data[] = $totalRow;

        return $data;
    }
}
