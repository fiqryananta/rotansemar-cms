<?php

namespace App\Exports;

use App\Exports\Sheets\RekapKelurahanByPermintaanSheet;
use App\Exports\Sheets\RekapKelurahanSheet;
use App\Exports\Sheets\RekapOpdSheet;
use App\Exports\Sheets\RekapPermintaanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements WithMultipleSheets
{
    public function __construct(
        private string $dari,
        private string $sampai,
    ) {}

    public function sheets(): array
    {
        return [
            new RekapOpdSheet($this->dari, $this->sampai),
            new RekapPermintaanSheet($this->dari, $this->sampai),
            new RekapKelurahanSheet($this->dari, $this->sampai),
            new RekapKelurahanByPermintaanSheet($this->dari, $this->sampai),
        ];
    }
}
