<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $pekerjaan = Pekerjaan::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Pekerjaan/Index', [
            'pekerjaan' => $pekerjaan,
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pekerjaan/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:pekerjaan,name',
        ]);

        Pekerjaan::create($validated);

        return redirect()->route('pekerjaan.index')->with('success', 'Pekerjaan created successfully');
    }

    public function edit(Pekerjaan $pekerjaan)
    {
        return Inertia::render('Admin/Pekerjaan/Edit', [
            'pekerjaan' => $pekerjaan,
        ]);
    }

    public function update(Request $request, Pekerjaan $pekerjaan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:pekerjaan,name,' . $pekerjaan->id,
        ]);

        $pekerjaan->update($validated);

        return redirect()->route('pekerjaan.index')->with('success', 'Pekerjaan updated successfully');
    }

    public function destroy(Pekerjaan $pekerjaan)
    {
        $pekerjaan->delete();

        return redirect()->route('pekerjaan.index')->with('success', 'Pekerjaan deleted successfully');
    }
}
