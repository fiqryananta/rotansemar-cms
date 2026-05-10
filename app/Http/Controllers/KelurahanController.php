<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KelurahanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $kelurahans = Kelurahan::query()
            ->with('kecamatan')
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kecamatan', function ($subQuery) use ($request) {
                        $subQuery->where('name', 'like', '%' . $request->search . '%');
                    });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $kecamatans = Kecamatan::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Kelurahans/Index', [
            'kelurahans' => $kelurahans,
            'kecamatans' => $kecamatans,
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kelurahans', 'name')->where(fn ($query) => $query->where('kecamatan_id', $request->kecamatan_id)),
            ],
        ]);

        Kelurahan::create($validated);

        return redirect()->route('kelurahans.index')->with('success', 'Kelurahan created successfully');
    }

    public function create()
    {
        $kecamatans = Kecamatan::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Kelurahans/Create', [
            'kecamatans' => $kecamatans,
        ]);
    }

    public function update(Request $request, Kelurahan $kelurahan)
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kelurahans', 'name')
                    ->where(fn ($query) => $query->where('kecamatan_id', $request->kecamatan_id))
                    ->ignore($kelurahan->id),
            ],
        ]);

        $kelurahan->update($validated);

        return redirect()->route('kelurahans.index')->with('success', 'Kelurahan updated successfully');
    }

    public function edit(Kelurahan $kelurahan)
    {
        $kecamatans = Kecamatan::query()->orderBy('name')->get(['id', 'name']);
        $kelurahan->load('kecamatan:id,name');

        return Inertia::render('Admin/Kelurahans/Edit', [
            'kelurahan' => $kelurahan,
            'kecamatans' => $kecamatans,
        ]);
    }

    public function destroy(Kelurahan $kelurahan)
    {
        $kelurahan->delete();

        return redirect()->route('kelurahans.index')->with('success', 'Kelurahan deleted successfully');
    }
}
