<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPenyuluhan;
use App\Support\ApiResponse;
use App\Support\MobileAccessScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KegiatanPenyuluhanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = KegiatanPenyuluhan::query()
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = (string) $request->input('search');
                $builder->where(function ($inner) use ($search) {
                    $inner->where('nama_kegiatan', 'like', "%{$search}%")
                        ->orWhere('lokasi_kegiatan', 'like', "%{$search}%")
                        ->orWhere('sasaran', 'like', "%{$search}%")
                        ->orWhere('uraian_kegiatan', 'like', "%{$search}%");
                });
            })
            ->latest('tanggal_kegiatan')
            ->latest('id')
            ->paginate($perPage)
            ->through(fn (KegiatanPenyuluhan $item) => $this->transform($item));

        return response()->json(ApiResponse::paginated('Daftar kegiatan penyuluhan.', $query));
    }

    public function show(Request $request, KegiatanPenyuluhan $kegiatanPenyuluhan): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        return response()->json(ApiResponse::success('Detail kegiatan penyuluhan.', $this->transform($kegiatanPenyuluhan)));
    }

    public function store(Request $request): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date'],
            'uraian_kegiatan' => ['required', 'string'],
            'lokasi_kegiatan' => ['required', 'string', 'max:255'],
            'koordinat_lokasi' => ['nullable', 'string', 'max:255'],
            'sasaran' => ['required', 'string', 'max:255'],
            'jumlah_sasaran' => ['required', 'integer', 'min:1'],
            'foto_kegiatan' => ['required', 'array', 'min:1'],
            'foto_kegiatan.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'foto_kegiatan.required' => 'Minimal 1 foto wajib diunggah.',
            'foto_kegiatan.min' => 'Minimal 1 foto wajib diunggah.',
        ]);

        $created = DB::transaction(function () use ($request, $validated) {
            $storedPhotos = [];

            foreach ($request->file('foto_kegiatan', []) as $file) {
                $storedPhotos[] = $file->store('kegiatan-penyuluhan', 'public');
            }

            $koordinatLokasi = filled($validated['koordinat_lokasi'] ?? null) ? $validated['koordinat_lokasi'] : null;

            return KegiatanPenyuluhan::create([
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
                'uraian_kegiatan' => $validated['uraian_kegiatan'],
                'lokasi_kegiatan' => $validated['lokasi_kegiatan'],
                'koordinat_lokasi' => $koordinatLokasi,
                'sasaran' => $validated['sasaran'],
                'jumlah_sasaran' => $validated['jumlah_sasaran'],
                'foto_kegiatan' => $storedPhotos,
            ]);
        });

        return response()->json(ApiResponse::success('Kegiatan penyuluhan berhasil disimpan.', $this->transform($created)), 201);
    }

    public function update(Request $request, KegiatanPenyuluhan $kegiatanPenyuluhan): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date'],
            'uraian_kegiatan' => ['required', 'string'],
            'lokasi_kegiatan' => ['required', 'string', 'max:255'],
            'koordinat_lokasi' => ['nullable', 'string', 'max:255'],
            'sasaran' => ['required', 'string', 'max:255'],
            'jumlah_sasaran' => ['required', 'integer', 'min:1'],
            'existing_foto_kegiatan' => ['required', 'array', 'min:1'],
            'existing_foto_kegiatan.*' => ['string'],
            'foto_kegiatan' => ['nullable', 'array'],
            'foto_kegiatan.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'existing_foto_kegiatan.required' => 'Minimal 1 foto harus dipertahankan.',
            'existing_foto_kegiatan.min' => 'Minimal 1 foto harus dipertahankan.',
        ]);

        $updated = DB::transaction(function () use ($request, $validated, $kegiatanPenyuluhan) {
            $existingPhotos = $validated['existing_foto_kegiatan'] ?? [];
            $originalPhotos = $kegiatanPenyuluhan->foto_kegiatan ?? [];
            $removedPhotos = array_values(array_diff($originalPhotos, $existingPhotos));
            $koordinatLokasi = filled($validated['koordinat_lokasi'] ?? null) ? $validated['koordinat_lokasi'] : null;

            foreach ($removedPhotos as $removedPhoto) {
                Storage::disk('public')->delete($removedPhoto);
            }

            $newPhotos = [];
            foreach ($request->file('foto_kegiatan', []) as $file) {
                $newPhotos[] = $file->store('kegiatan-penyuluhan', 'public');
            }

            $kegiatanPenyuluhan->update([
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
                'uraian_kegiatan' => $validated['uraian_kegiatan'],
                'lokasi_kegiatan' => $validated['lokasi_kegiatan'],
                'koordinat_lokasi' => $koordinatLokasi,
                'sasaran' => $validated['sasaran'],
                'jumlah_sasaran' => $validated['jumlah_sasaran'],
                'foto_kegiatan' => array_values(array_merge($existingPhotos, $newPhotos)),
            ]);

            return $kegiatanPenyuluhan->refresh();
        });

        return response()->json(ApiResponse::success('Kegiatan penyuluhan berhasil diperbarui.', $this->transform($updated)));
    }

    public function destroy(Request $request, KegiatanPenyuluhan $kegiatanPenyuluhan): JsonResponse
    {
        $access = MobileAccessScope::accessMatrix($request->user());

        if (!$access['can_access_wilker']) {
            return response()->json(ApiResponse::error('Anda tidak memiliki akses domain WILKER.', [], 403), 403);
        }

        foreach (($kegiatanPenyuluhan->foto_kegiatan ?? []) as $photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $kegiatanPenyuluhan->delete();

        return response()->json(ApiResponse::success('Kegiatan penyuluhan berhasil dihapus.'));
    }

    private function transform(KegiatanPenyuluhan $item): array
    {
        $photos = collect($item->foto_kegiatan ?? [])
            ->map(fn (string $path) => Storage::disk('public')->url($path))
            ->values()
            ->all();

        return [
            'id' => $item->id,
            'nama_kegiatan' => $item->nama_kegiatan,
            'tanggal_kegiatan' => $item->tanggal_kegiatan?->format('Y-m-d'),
            'uraian_kegiatan' => $item->uraian_kegiatan,
            'lokasi_kegiatan' => $item->lokasi_kegiatan,
            'koordinat_lokasi' => $item->koordinat_lokasi,
            'sasaran' => $item->sasaran,
            'jumlah_sasaran' => $item->jumlah_sasaran,
            'foto_kegiatan' => $photos,
            'created_at' => optional($item->created_at)?->toISOString(),
        ];
    }
}