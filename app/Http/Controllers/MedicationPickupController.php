<?php

namespace App\Http\Controllers;

use App\Models\Faskes;
use App\Models\MedicationPickup;
use App\Models\Pasien;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MedicationPickupController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = MedicationPickup::query()
            ->with([
                'pasien:id,name,nik',
                'faskes:id,name',
                'puskesmas:id,name',
                'targetFaskes:id,name',
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('pasien', fn ($pq) => $pq->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%"))
                    ->orWhereHas('faskes', fn ($fq) => $fq->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'pickups' => $query,
            'links' => $query->linkCollection(),
            'statuses' => [
                'menunggu' => 'Menunggu',
                'terrealisasi' => 'Terealisasi',
                'pindah' => 'Pindah',
                'putus_obat' => 'Putus Obat',
                'meninggal' => 'Meninggal',
                'obat_terakhir' => 'Obat Terakhir',
            ],
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('medication-pickups.index', $payload);
    }

    public function create()
    {
        $payload = [
            'pickup' => null,
            'pasiens' => Pasien::query()->select('id', 'name', 'nik')->orderBy('name')->get(),
            'faskes' => Faskes::query()->select('id', 'name')->orderBy('name')->get(),
            'puskesmas' => Puskesmas::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => [
                'menunggu' => 'Menunggu',
                'terrealisasi' => 'Terealisasi',
                'pindah' => 'Pindah',
                'putus_obat' => 'Putus Obat',
                'meninggal' => 'Meninggal',
                'obat_terakhir' => 'Obat Terakhir',
            ],
        ];

        return view('medication-pickups.create', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pasien_id' => ['required', 'exists:pasiens,id'],
            'faskes_id' => ['nullable', 'exists:faskes,id'],
            'puskesmas_id' => ['nullable', 'exists:puskesmas,id'],
            'scheduled_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['menunggu', 'terrealisasi', 'pindah', 'putus_obat', 'meninggal', 'obat_terakhir'])],
            'actual_date' => ['nullable', 'date'],
            'transfer_date' => ['nullable', 'date'],
            'next_pickup_date' => ['nullable', 'date'],
            'target_faskes_id' => ['nullable', 'exists:faskes,id'],
            'is_outside_city' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        MedicationPickup::create($validated);

        return redirect()->route('medication-pickups.index')
            ->with('success', 'Pengambilan obat berhasil ditambahkan.');
    }

    public function edit(MedicationPickup $medicationPickup)
    {
        $payload = [
            'pickup' => $medicationPickup->load(['pasien:id,name,nik', 'faskes:id,name', 'puskesmas:id,name', 'targetFaskes:id,name']),
            'pasiens' => Pasien::query()->select('id', 'name', 'nik')->orderBy('name')->get(),
            'faskes' => Faskes::query()->select('id', 'name')->orderBy('name')->get(),
            'puskesmas' => Puskesmas::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => [
                'menunggu' => 'Menunggu',
                'terrealisasi' => 'Terealisasi',
                'pindah' => 'Pindah',
                'putus_obat' => 'Putus Obat',
                'meninggal' => 'Meninggal',
                'obat_terakhir' => 'Obat Terakhir',
            ],
        ];

        return view('medication-pickups.edit', $payload);
    }

    public function update(Request $request, MedicationPickup $medicationPickup)
    {
        $validated = $request->validate([
            'pasien_id' => ['required', 'exists:pasiens,id'],
            'faskes_id' => ['nullable', 'exists:faskes,id'],
            'puskesmas_id' => ['nullable', 'exists:puskesmas,id'],
            'scheduled_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['menunggu', 'terrealisasi', 'pindah', 'putus_obat', 'meninggal', 'obat_terakhir'])],
            'actual_date' => ['nullable', 'date'],
            'transfer_date' => ['nullable', 'date'],
            'next_pickup_date' => ['nullable', 'date'],
            'target_faskes_id' => ['nullable', 'exists:faskes,id'],
            'is_outside_city' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $medicationPickup->update($validated);

        return redirect()->route('medication-pickups.index')
            ->with('success', 'Pengambilan obat berhasil diperbarui.');
    }

    public function destroy(MedicationPickup $medicationPickup)
    {
        $medicationPickup->delete();

        return redirect()->route('medication-pickups.index')
            ->with('success', 'Pengambilan obat berhasil dihapus.');
    }
}


