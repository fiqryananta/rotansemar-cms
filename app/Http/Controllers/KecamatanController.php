<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

        return Inertia::render('Admin/Kecamatans/Index', [
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
            'name' => 'required|string|max:255|unique:kecamatans,name',
        ]);

        Kecamatan::create($validated);

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan created successfully');
    }

    public function create()
    {
        return Inertia::render('Admin/Kecamatans/Create');
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
        return Inertia::render('Admin/Kecamatans/Edit', [
            'kecamatan' => $kecamatan,
        ]);
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
