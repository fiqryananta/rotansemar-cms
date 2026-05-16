<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Throwable;

class KecamatanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $kecamatans = Kecamatan::query()
            ->withCount('kelurahans')
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'kecamatans' => $kecamatans,
            'links' => $kecamatans->linkCollection(),
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('kecamatans.index', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:kecamatans,name',
        ]);

        Kecamatan::create($validated);

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan created successfully');
    }

    public function create()
    {
        return view('kecamatans.create');
    }

    public function update(Request $request, Kecamatan $kecamatan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:kecamatans,name,' . $kecamatan->id,
        ]);

        $kecamatan->update($validated);

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan updated successfully');
    }

    public function edit(Kecamatan $kecamatan)
    {
        $payload = [
            'kecamatan' => $kecamatan,
        ];

        return view('kecamatans.edit', $payload);
    }

    public function destroy(Kecamatan $kecamatan)
    {
        try {
            $kecamatan->delete();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('kecamatans.index')
                ->with('error', 'Kecamatan gagal dihapus karena masih dipakai oleh data lain.');
        }

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan deleted successfully');
    }
}


