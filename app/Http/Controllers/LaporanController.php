<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'dari'   => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
        ]);

        $dari   = $request->dari;
        $sampai = $request->sampai;

        $filename = 'laporan_' . $dari . '_sd_' . $sampai . '.xlsx';

        return Excel::download(new LaporanExport($dari, $sampai), $filename);
    }
}


