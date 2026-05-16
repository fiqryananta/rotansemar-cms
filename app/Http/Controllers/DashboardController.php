<?php

namespace App\Http\Controllers;

use App\Models\JenisKebutuhan;
use App\Models\PasienKebutuhan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = $this->applyDashboardScope(PasienKebutuhan::query(), $request->user());

        $rawStatusCounts = (clone $baseQuery)
            ->selectRaw('verification_status, COUNT(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        $statusCounts = [
            'pending' => (int) ($rawStatusCounts['pending'] ?? 0),
            'proses' => (int) ($rawStatusCounts['proses'] ?? 0),
            'pending_bantuan' => (int) ($rawStatusCounts['pending_bantuan'] ?? 0),
            'tidak_layak' => (int) ($rawStatusCounts['tidak_layak'] ?? 0),
            'selesai' => (int) ($rawStatusCounts['selesai'] ?? 0),
        ];

        $kebutuhanRows = (clone $baseQuery)
            ->selectRaw(
                "jenis_kebutuhan_id,
                COUNT(DISTINCT pasien_id) as total_pasien,
                SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN verification_status = 'proses' THEN 1 ELSE 0 END) as proses_count,
                SUM(CASE WHEN verification_status = 'pending_bantuan' THEN 1 ELSE 0 END) as pending_bantuan_count,
                SUM(CASE WHEN verification_status = 'tidak_layak' THEN 1 ELSE 0 END) as tidak_layak_count,
                SUM(CASE WHEN verification_status = 'selesai' THEN 1 ELSE 0 END) as selesai_count"
            )
            ->whereNotNull('jenis_kebutuhan_id')
            ->groupBy('jenis_kebutuhan_id')
            ->orderByDesc('total_pasien')
            ->get();

        $jenisKebutuhanNames = JenisKebutuhan::query()
            ->whereIn('id', $kebutuhanRows->pluck('jenis_kebutuhan_id')->filter()->all())
            ->pluck('name', 'id');

        $kebutuhanSummary = $kebutuhanRows->map(function ($row) use ($jenisKebutuhanNames) {
            return [
                'jenis_kebutuhan_id' => (int) $row->jenis_kebutuhan_id,
                'jenis_kebutuhan_name' => $jenisKebutuhanNames[$row->jenis_kebutuhan_id] ?? '-',
                'total_pasien' => (int) $row->total_pasien,
                'pending_count' => (int) $row->pending_count,
                'proses_count' => (int) $row->proses_count,
                'pending_bantuan_count' => (int) $row->pending_bantuan_count,
                'tidak_layak_count' => (int) $row->tidak_layak_count,
                'selesai_count' => (int) $row->selesai_count,
            ];
        })->values();

        $cards = [
            [
                'label'       => 'Pending',
                'value'       => $statusCounts['pending'],
                'accent'      => 'bg-gray-100 text-gray-700',
                'icon'        => 'ti-clock',
                'icon_bg'     => 'bg-slate-100',
                'icon_color'  => 'text-slate-500',
                'value_color' => 'text-slate-800',
                'bar'         => 'bg-slate-400',
            ],
            [
                'label'       => 'Proses',
                'value'       => $statusCounts['proses'],
                'accent'      => 'bg-blue-100 text-blue-700',
                'icon'        => 'ti-loader',
                'icon_bg'     => 'bg-blue-100',
                'icon_color'  => 'text-blue-600',
                'value_color' => 'text-blue-800',
                'bar'         => 'bg-blue-500',
            ],
            [
                'label'       => 'Pending Bantuan',
                'value'       => $statusCounts['pending_bantuan'],
                'accent'      => 'bg-amber-100 text-amber-700',
                'icon'        => 'ti-alert-triangle',
                'icon_bg'     => 'bg-amber-100',
                'icon_color'  => 'text-amber-600',
                'value_color' => 'text-amber-800',
                'bar'         => 'bg-amber-500',
            ],
            [
                'label'       => 'Tidak Layak',
                'value'       => $statusCounts['tidak_layak'],
                'accent'      => 'bg-rose-100 text-rose-700',
                'icon'        => 'ti-circle-x',
                'icon_bg'     => 'bg-rose-100',
                'icon_color'  => 'text-rose-600',
                'value_color' => 'text-rose-800',
                'bar'         => 'bg-rose-500',
            ],
            [
                'label'       => 'Selesai',
                'value'       => $statusCounts['selesai'],
                'accent'      => 'bg-emerald-100 text-emerald-700',
                'icon'        => 'ti-circle-check',
                'icon_bg'     => 'bg-emerald-100',
                'icon_color'  => 'text-emerald-600',
                'value_color' => 'text-emerald-800',
                'bar'         => 'bg-emerald-500',
            ],
        ];

        $totalStatus = array_sum($statusCounts);

        return view('dashboard.index', [
            'statusCounts'     => $statusCounts,
            'kebutuhanSummary' => $kebutuhanSummary,
            'cards'            => $cards,
            'totalStatus'      => $totalStatus,
        ]);
    }

    private function applyDashboardScope($query, $user)
    {
        if (!$user) {
            return $query;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return $query;
        }

        if ($roles->contains('opd') && $user->opd_id) {
            return $query->where('opd_id', $user->opd_id);
        }

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            return $query->whereHas('pasien', fn ($pq) => $pq->where('puskesmas_id', $user->puskesmas_id));
        }

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            return $query->whereHas('pasien', fn ($pq) => $pq->where('kecamatan_id', $user->kecamatan_id));
        }

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            return $query->whereHas('pasien', fn ($pq) => $pq->where('kelurahan_id', $user->kelurahan_id));
        }

        return $query->whereRaw('1 = 0');
    }
}


