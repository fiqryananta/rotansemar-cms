<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\PasienKebutuhan;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KebutuhanController extends Controller
{
    public function show(Request $request, PasienKebutuhan $pasienKebutuhan): JsonResponse
    {
        if (!$this->canAccessTindakLanjut($request->user(), $pasienKebutuhan)) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses data kebutuhan ini.', [], 403), 403);
        }

        $pasienKebutuhan->load([
            'pasien:id,name,nik,address,catatan_kebutuhan',
            'opd:id,name',
            'jenisPenanganan:id,name',
            'jenisKebutuhan:id,name',
            'tindakLanjuts' => fn ($q) => $q->with(['user:id,name,email', 'fotos:id,tindak_lanjut_id,path']),
        ]);

        return response()->json(ApiResponse::success('Detail kebutuhan pasien.', [
            'id' => $pasienKebutuhan->id,
            'verification_status' => $pasienKebutuhan->verification_status,
            'need_detail' => $pasienKebutuhan->need_detail,
            'pasien' => [
                'id' => $pasienKebutuhan->pasien?->id,
                'name' => $pasienKebutuhan->pasien?->name,
                'nik' => $pasienKebutuhan->pasien?->nik,
                'address' => $pasienKebutuhan->pasien?->address,
                'catatan_kebutuhan' => $pasienKebutuhan->pasien?->catatan_kebutuhan,
            ],
            'jenis_kebutuhan' => $pasienKebutuhan->jenisKebutuhan?->name,
            'jenis_penanganan' => $pasienKebutuhan->jenisPenanganan?->name,
            'opd' => $pasienKebutuhan->opd?->name,
            'tindak_lanjuts' => $pasienKebutuhan->tindakLanjuts->map(function ($item) {
                return [
                    'id' => $item->id,
                    'keterangan' => $item->keterangan,
                    'created_at' => optional($item->created_at)?->toISOString(),
                    'user' => [
                        'id' => $item->user?->id,
                        'name' => $item->user?->name,
                        'email' => $item->user?->email,
                    ],
                    'fotos' => $item->fotos->map(function ($foto) {
                        return [
                            'id' => $foto->id,
                            'path' => $foto->path,
                            'url' => $foto->url,
                        ];
                    })->values(),
                ];
            })->values(),
        ]));
    }

    public function verify(Request $request, PasienKebutuhan $pasienKebutuhan): JsonResponse
    {
        if (!$this->canAccessTindakLanjut($request->user(), $pasienKebutuhan)) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses data kebutuhan ini.', [], 403), 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['proses', 'pending_bantuan', 'tidak_layak'])],
        ]);

        $pasienKebutuhan->update(['verification_status' => $validated['status']]);

        return response()->json(ApiResponse::success('Status verifikasi berhasil diperbarui.', [
            'id' => $pasienKebutuhan->id,
            'verification_status' => $pasienKebutuhan->verification_status,
        ]));
    }

    public function addFollowUp(Request $request, PasienKebutuhan $pasienKebutuhan): JsonResponse
    {
        if (!$this->canAccessTindakLanjut($request->user(), $pasienKebutuhan)) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses data kebutuhan ini.', [], 403), 403);
        }

        if (!in_array($pasienKebutuhan->verification_status, ['proses', 'pending_bantuan'], true)) {
            return response()->json(ApiResponse::error('Tidak dapat menambahkan tindak lanjut pada status ini.', [], 422), 422);
        }

        $validated = $request->validate([
            'keterangan' => ['required', 'string', 'max:2000'],
            'fotos' => ['required', 'array', 'min:1'],
            'fotos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'fotos.required' => 'Minimal 1 foto wajib diunggah.',
            'fotos.min' => 'Minimal 1 foto wajib diunggah.',
            'fotos.*.image' => 'File harus berupa gambar.',
            'fotos.*.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'fotos.*.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $created = DB::transaction(function () use ($request, $pasienKebutuhan, $validated) {
            $tindakLanjut = $pasienKebutuhan->tindakLanjuts()->create([
                'keterangan' => $validated['keterangan'],
                'user_id' => $request->user()->id,
            ]);

            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('tindak-lanjut/' . $tindakLanjut->id, 'public');
                $tindakLanjut->fotos()->create(['path' => $path]);
            }

            return $tindakLanjut->load(['user:id,name,email', 'fotos:id,tindak_lanjut_id,path']);
        });

        return response()->json(ApiResponse::success('Tindak lanjut berhasil ditambahkan.', [
            'id' => $created->id,
            'keterangan' => $created->keterangan,
            'created_at' => optional($created->created_at)?->toISOString(),
            'user' => [
                'id' => $created->user?->id,
                'name' => $created->user?->name,
                'email' => $created->user?->email,
            ],
            'fotos' => $created->fotos->map(function ($foto) {
                return [
                    'id' => $foto->id,
                    'path' => $foto->path,
                    'url' => $foto->url,
                ];
            })->values(),
        ]));
    }

    public function markDone(Request $request, PasienKebutuhan $pasienKebutuhan): JsonResponse
    {
        if (!$this->canAccessTindakLanjut($request->user(), $pasienKebutuhan)) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses data kebutuhan ini.', [], 403), 403);
        }

        if (!in_array($pasienKebutuhan->verification_status, ['proses', 'pending_bantuan'], true)) {
            return response()->json(ApiResponse::error('Status tidak valid untuk diselesaikan.', [], 422), 422);
        }

        if ($pasienKebutuhan->tindakLanjuts()->count() === 0) {
            return response()->json(ApiResponse::error('Harus ada minimal satu riwayat tindak lanjut sebelum dapat diselesaikan.', [], 422), 422);
        }

        $pasienKebutuhan->update(['verification_status' => 'selesai']);

        return response()->json(ApiResponse::success('Penanganan telah diselesaikan.', [
            'id' => $pasienKebutuhan->id,
            'verification_status' => $pasienKebutuhan->verification_status,
        ]));
    }

    private function canAccessTindakLanjut($user, PasienKebutuhan $pasienKebutuhan): bool
    {
        if (!$user) {
            return false;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return true;
        }

        if ($roles->contains('opd')) {
            return (int) $user->opd_id === (int) $pasienKebutuhan->opd_id;
        }

        $pasienKebutuhan->loadMissing('pasien:id,puskesmas_id,kecamatan_id,kelurahan_id');

        if ($roles->contains('puskesmas')) {
            return (int) $user->puskesmas_id === (int) $pasienKebutuhan->pasien?->puskesmas_id;
        }

        if ($roles->contains('kecamatan')) {
            return (int) $user->kecamatan_id === (int) $pasienKebutuhan->pasien?->kecamatan_id;
        }

        if ($roles->contains('kelurahan')) {
            return (int) $user->kelurahan_id === (int) $pasienKebutuhan->pasien?->kelurahan_id;
        }

        return false;
    }
}
