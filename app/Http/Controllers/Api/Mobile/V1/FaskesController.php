<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\Faskes;
use App\Models\MedicationPickup;
use App\Models\Pasien;
use App\Models\VisitResult;
use App\Support\ApiResponse;
use App\Support\MobileAccessScope;
use App\Support\MobilePatientTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaskesController extends Controller
{
    public function patients(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_faskes']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain FASKES.', [], 403), 403);
        }

        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = MobileAccessScope::scopeFaskesPatients(MobileAccessScope::pasienBaseQuery(), $request->user())
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
                    'treatment_start_date' => $pasien->treatment_start_date,
                    'treatment_status' => $pasien->treatment_status,
                    'faskes' => $pasien->faskes?->name,
                    'puskesmas' => $pasien->puskesmas?->name,
                ];
            });

        return response()->json(ApiResponse::paginated('Daftar pasien domain FASKES.', $query));
    }

    public function show(Request $request, Pasien $pasien): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_faskes']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain FASKES.', [], 403), 403);
        }

        $patient = MobileAccessScope::scopeFaskesPatients(MobileAccessScope::pasienBaseQuery(), $request->user())
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

        return response()->json(ApiResponse::success('Detail pasien domain FASKES.', MobilePatientTransformer::detail($patient)));
    }

    /**
     * Get medication pickups for FASKES (scheduled for a specific date)
     */
    public function medicationPickups(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_faskes']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain FASKES.', [], 403), 403);
        }

        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $date = $request->input('date') ?? now()->format('Y-m-d');

        $faskesId = $request->user()->faskes_id ?? $request->user()->puskesmas?->faskes_id;

        $pickups = MedicationPickup::query()
            ->where('faskes_id', $faskesId)
            ->whereDate('scheduled_date', $date)
            ->with(['pasien:id,name,nik', 'faskes:id,name'])
            ->orderBy('scheduled_date')
            ->get()
            ->map(function ($pickup) {
                return [
                    'id' => $pickup->id,
                    'pasien_id' => $pickup->pasien_id,
                    'pasien_name' => $pickup->pasien->name,
                    'pasien_nik' => $pickup->pasien->nik,
                    'scheduled_date' => $pickup->scheduled_date?->format('Y-m-d'),
                    'actual_date' => $pickup->actual_date?->format('Y-m-d'),
                    'status' => $pickup->status,
                    'next_pickup_date' => $pickup->next_pickup_date?->format('Y-m-d'),
                    'transfer_date' => $pickup->transfer_date?->format('Y-m-d'),
                    'notes' => $pickup->notes,
                ];
            });

        return response()->json(ApiResponse::success('Daftar pengambilan obat FASKES.', $pickups));
    }

    /**
     * Update medication pickup status
     */
    public function updateMedicationPickupStatus(Request $request, MedicationPickup $pickup): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_faskes']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain FASKES.', [], 403), 403);
        }

        $faskesId = $request->user()->faskes_id ?? $request->user()->puskesmas?->faskes_id;
        if ((int) $pickup->faskes_id !== (int) $faskesId) {
            return response()->json(ApiResponse::error('Data pengambilan obat tidak ditemukan pada faskes Anda.', [], 404), 404);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['menunggu', 'terrealisasi', 'pindah', 'putus_obat', 'meninggal', 'obat_terakhir']),
            ],
            'actual_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'transfer_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'next_pickup_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'target_faskes_id' => 'nullable|exists:faskes,id',
            'is_outside_city' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'terrealisasi') {
            if (empty($validated['actual_date'])) {
                return response()->json(ApiResponse::error('Tanggal realisasi wajib diisi untuk status terealisasi.', [], 422), 422);
            }
            if (empty($validated['next_pickup_date'])) {
                return response()->json(ApiResponse::error('Jadwal pengambilan obat selanjutnya wajib diisi untuk status terealisasi.', [], 422), 422);
            }
        }

        if ($validated['status'] === 'pindah') {
            if (empty($validated['transfer_date'])) {
                return response()->json(ApiResponse::error('Tanggal pindah pasien wajib diisi.', [], 422), 422);
            }
            if (empty($validated['notes'])) {
                return response()->json(ApiResponse::error('Keterangan/alasan pindah wajib diisi.', [], 422), 422);
            }
            if (empty($validated['next_pickup_date'])) {
                return response()->json(ApiResponse::error('Rencana pasien ke faskes tujuan wajib diisi.', [], 422), 422);
            }
            if (!isset($validated['target_faskes_id']) && !($validated['is_outside_city'] ?? false)) {
                return response()->json(ApiResponse::error('Faskes tujuan atau pindah luar kota wajib dipilih.', [], 422), 422);
            }
        }

        if (in_array($validated['status'], ['putus_obat', 'meninggal', 'obat_terakhir'], true) && empty($validated['notes'])) {
            return response()->json(ApiResponse::error('Keterangan/catatan wajib diisi untuk status yang dipilih.', [], 422), 422);
        }

        if ($validated['status'] === 'obat_terakhir' && empty($validated['actual_date'])) {
            return response()->json(ApiResponse::error('Tanggal pengambilan obat wajib diisi untuk status obat terakhir.', [], 422), 422);
        }

        $status = $validated['status'];
        $updateData = [
            'status' => $status,
            'actual_date' => null,
            'next_pickup_date' => null,
            'transfer_date' => null,
            'death_date' => null,
            'target_faskes_id' => null,
            'is_outside_city' => false,
            'notes' => null,
        ];

        if ($status === 'terrealisasi') {
            $updateData['actual_date'] = $validated['actual_date'];
            $updateData['next_pickup_date'] = $validated['next_pickup_date'];
        } elseif ($status === 'pindah') {
            $updateData['transfer_date'] = $validated['transfer_date'];
            $updateData['next_pickup_date'] = $validated['next_pickup_date'];
            $updateData['target_faskes_id'] = $validated['target_faskes_id'] ?? null;
            $updateData['is_outside_city'] = $validated['is_outside_city'] ?? false;
            $updateData['notes'] = $validated['notes'];

            if (!empty($validated['target_faskes_id'])) {
                $pickup->pasien->update(['faskes_id' => $validated['target_faskes_id']]);
            }
        } elseif ($status === 'putus_obat') {
            $updateData['notes'] = $validated['notes'];
        } elseif ($status === 'meninggal') {
            $updateData['notes'] = $validated['notes'];
            $pickup->pasien->update(['treatment_status' => 'selesai']);
        } elseif ($status === 'obat_terakhir') {
            $updateData['actual_date'] = $validated['actual_date'];
            $updateData['notes'] = $validated['notes'];
            $pickup->pasien->update(['treatment_status' => 'selesai']);
        }

        $pickup->update($updateData);

        return response()->json(ApiResponse::success('Status pengambilan obat berhasil diperbarui.', [
            'id' => $pickup->id,
            'status' => $pickup->status,
            'message' => $this->getStatusMessage($pickup->status),
        ]));
    }

    /**
     * Get visit results for FASKES
     */
    public function visitResults(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_faskes']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain FASKES.', [], 403), 403);
        }

        $request->validate([
            'visit_type' => 'nullable|in:investigasi_kasus,kunjungan_rumah,kunjungan_mangkir',
            'per_page' => 'integer|in:10,25,50',
        ]);

        $perPage = (int) $request->input('per_page', 10);
        $faskesId = $request->user()->faskes_id;

        $query = VisitResult::query()
            ->where('faskes_id', $faskesId)
            ->when($request->filled('visit_type'), function ($q) use ($request) {
                $q->where('visit_type', $request->input('visit_type'));
            })
            ->with(['pasien:id,name,nik', 'user:id,name'])
            ->orderByDesc('visit_date')
            ->paginate($perPage)
            ->through(function ($visit) {
                return [
                    'id' => $visit->id,
                    'pasien_id' => $visit->pasien_id,
                    'pasien_name' => $visit->pasien->name,
                    'pasien_nik' => $visit->pasien->nik,
                    'visit_type' => $visit->visit_type,
                    'visit_date' => $visit->visit_date?->format('Y-m-d'),
                    'findings' => $visit->findings,
                    'follow_up' => $visit->follow_up,
                    'location_detail' => $visit->location_detail,
                    'user_name' => $visit->user->name,
                ];
            });

        return response()->json(ApiResponse::paginated('Daftar hasil kunjungan FASKES.', $query));
    }

    /**
     * Create visit result
     */
    public function storeVisitResult(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_faskes']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain FASKES.', [], 403), 403);
        }

        $validated = $request->validate([
            'pasien_id' => 'required|exists:pasiens,id',
            'visit_type' => 'required|in:investigasi_kasus,kunjungan_rumah,kunjungan_mangkir',
            'visit_date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'findings' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'location_detail' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_source' => 'nullable|in:gps,maps',
        ]);

        $faskesId = $request->user()->faskes_id;

        // Verify pasien belongs to this faskes
        $pasien = Pasien::find($validated['pasien_id']);
        if ($pasien->faskes_id !== $faskesId) {
            return response()->json(ApiResponse::error('Pasien tidak terdaftar di faskes Anda.', [], 403), 403);
        }

        $visitResult = VisitResult::create([
            'pasien_id' => $validated['pasien_id'],
            'faskes_id' => $faskesId,
            'visit_type' => $validated['visit_type'],
            'visit_date' => $validated['visit_date'],
            'findings' => $validated['findings'],
            'follow_up' => $validated['follow_up'],
            'location_detail' => $validated['location_detail'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'location_source' => $validated['location_source'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json(ApiResponse::success('Hasil kunjungan berhasil disimpan.', [
            'id' => $visitResult->id,
            'pasien_id' => $visitResult->pasien_id,
            'visit_type' => $visitResult->visit_type,
            'visit_date' => $visitResult->visit_date?->format('Y-m-d'),
        ]), 201);
    }

    /**
     * Get status message based on status code
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'menunggu' => 'Pengambilan obat menunggu realisasi.',
            'terrealisasi' => 'Pengambilan obat terealisasi. Jadwal berikutnya telah diatur.',
            'pindah' => 'Data pasien berhasil diperbarui sebagai pindah faskes.',
            'putus_obat' => 'Status pasien diperbarui sebagai putus obat.',
            'meninggal' => 'Data pasien diperbarui sebagai meninggal dunia.',
            'obat_terakhir' => 'Pengobatan pasien ditandai sebagai obat terakhir.',
            default => 'Status diperbarui.',
        };
    }
}

