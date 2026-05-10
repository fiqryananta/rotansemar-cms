<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\MobileAccessScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $baseQuery = MobileAccessScope::scopeDashboard(MobileAccessScope::kebutuhanBaseQuery(), $request->user());

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

        $totalPasien = (clone $baseQuery)->distinct('pasien_id')->count('pasien_id');
        $totalKebutuhan = (clone $baseQuery)->count();

        return response()->json(ApiResponse::success('Ringkasan dashboard mobile.', [
            'total_pasien' => (int) $totalPasien,
            'total_kebutuhan' => (int) $totalKebutuhan,
            'status_counts' => $statusCounts,
        ]));
    }
}
