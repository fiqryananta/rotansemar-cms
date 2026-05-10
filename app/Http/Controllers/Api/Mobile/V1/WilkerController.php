<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Support\ApiResponse;
use App\Support\MobileAccessScope;
use App\Support\MobilePatientTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilkerController extends Controller
{
    public function patients(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = MobileAccessScope::scopeWilkerPatients(MobileAccessScope::pasienBaseQuery(), $request->user())
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = (string) $request->input('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->through(function ($pasien) {
                return [
                    'id' => $pasien->id,
                    'name' => $pasien->name,
                    'nik' => $pasien->nik,
                    'address' => $pasien->address,
                    'coordinates' => $pasien->coordinates,
                    'kecamatan' => $pasien->kecamatan?->name,
                    'kelurahan' => $pasien->kelurahan?->name,
                    'puskesmas' => $pasien->puskesmas?->name,
                ];
            });

        return response()->json(ApiResponse::paginated('Daftar pasien domain WILKER.', $query));
    }

    public function show(Request $request, Pasien $pasien): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        $patient = MobileAccessScope::scopeWilkerPatients(MobileAccessScope::pasienBaseQuery(), $request->user())
            ->with([
                'kebutuhans:id,pasien_id,jenis_kebutuhan_id,opd_id,jenis_penanganan_id,need_detail,verification_status',
                'kebutuhans.jenisKebutuhan:id,name',
                'kebutuhans.opd:id,name',
                'kebutuhans.jenisPenanganan:id,name',
                'kebutuhans.tindakLanjuts' => fn ($q) => $q->with(['user:id,name,email', 'fotos:id,tindak_lanjut_id,path']),
            ])
            ->whereKey($pasien->id)
            ->first();

        if (!$patient) {
            return response()->json(ApiResponse::error('Data pasien tidak ditemukan.', [], 404), 404);
        }

        return response()->json(ApiResponse::success('Detail pasien domain WILKER.', MobilePatientTransformer::detail($patient)));
    }
}
