<?php

namespace App\Http\Controllers;

use App\Models\KegiatanPenyuluhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KegiatanPenyuluhanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $kegiatanPenyuluhans = KegiatanPenyuluhan::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama_kegiatan', 'like', '%' . $search . '%')
                    ->orWhere('lokasi_kegiatan', 'like', '%' . $search . '%')
                    ->orWhere('sasaran', 'like', '%' . $search . '%')
                    ->orWhere('uraian_kegiatan', 'like', '%' . $search . '%');
            })
            ->latest('tanggal_kegiatan')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/KegiatanPenyuluhan/Index', [
            'kegiatanPenyuluhans' => $kegiatanPenyuluhans,
            'filters' => [
                'search' => $search ?? '',
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/KegiatanPenyuluhan/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date'],
            'uraian_kegiatan' => ['required', 'string'],
            'lokasi_kegiatan' => ['required', 'string', 'max:255'],
            'koordinat_lokasi' => ['nullable', 'string', 'max:255'],
            'sasaran' => ['required', 'string', 'max:255'],
            'jumlah_sasaran' => ['required', 'integer', 'min:1'],
            'foto_kegiatan' => ['nullable', 'array'],
            'foto_kegiatan.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $storedPhotos = [];
        if ($request->hasFile('foto_kegiatan')) {
            foreach ($request->file('foto_kegiatan') as $file) {
                $storedPhotos[] = $file->store('kegiatan-penyuluhan', 'public');
            }
        }

        $koordinatLokasi = filled($validated['koordinat_lokasi'] ?? null) ? $validated['koordinat_lokasi'] : null;

        KegiatanPenyuluhan::create([
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
            'uraian_kegiatan' => $validated['uraian_kegiatan'],
            'lokasi_kegiatan' => $validated['lokasi_kegiatan'],
            'koordinat_lokasi' => $koordinatLokasi,
            'sasaran' => $validated['sasaran'],
            'jumlah_sasaran' => $validated['jumlah_sasaran'],
            'foto_kegiatan' => $storedPhotos,
        ]);

        return redirect()->route('kegiatan-penyuluhan.index')->with('success', 'Kegiatan penyuluhan created successfully');
    }

    public function edit(KegiatanPenyuluhan $kegiatanPenyuluhan)
    {
        return Inertia::render('Admin/KegiatanPenyuluhan/Edit', [
            'kegiatanPenyuluhan' => $kegiatanPenyuluhan,
        ]);
    }

    public function update(Request $request, KegiatanPenyuluhan $kegiatanPenyuluhan)
    {
        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date'],
            'uraian_kegiatan' => ['required', 'string'],
            'lokasi_kegiatan' => ['required', 'string', 'max:255'],
            'koordinat_lokasi' => ['nullable', 'string', 'max:255'],
            'sasaran' => ['required', 'string', 'max:255'],
            'jumlah_sasaran' => ['required', 'integer', 'min:1'],
            'existing_foto_kegiatan' => ['nullable', 'array'],
            'existing_foto_kegiatan.*' => ['string'],
            'foto_kegiatan' => ['nullable', 'array'],
            'foto_kegiatan.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $existingPhotos = $validated['existing_foto_kegiatan'] ?? [];
        $originalPhotos = $kegiatanPenyuluhan->foto_kegiatan ?? [];

        $removedPhotos = array_values(array_diff($originalPhotos, $existingPhotos));
        foreach ($removedPhotos as $removedPhoto) {
            Storage::disk('public')->delete($removedPhoto);
        }

        $newPhotos = [];
        if ($request->hasFile('foto_kegiatan')) {
            foreach ($request->file('foto_kegiatan') as $file) {
                $newPhotos[] = $file->store('kegiatan-penyuluhan', 'public');
            }
        }

        $koordinatLokasi = filled($validated['koordinat_lokasi'] ?? null) ? $validated['koordinat_lokasi'] : null;

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

        return redirect()->route('kegiatan-penyuluhan.index')->with('success', 'Kegiatan penyuluhan updated successfully');
    }

    public function destroy(KegiatanPenyuluhan $kegiatanPenyuluhan)
    {
        foreach (($kegiatanPenyuluhan->foto_kegiatan ?? []) as $photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $kegiatanPenyuluhan->delete();

        return redirect()->route('kegiatan-penyuluhan.index')->with('success', 'Kegiatan penyuluhan deleted successfully');
    }
}
