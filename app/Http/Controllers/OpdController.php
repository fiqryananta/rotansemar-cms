<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $opds = Opd::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'opds' => $opds,
            'links' => $opds->linkCollection(),
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('opds.index', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:opds,name',
        ]);

        Opd::create($validated);

        return redirect()->route('opds.index')->with('success', 'OPD created successfully');
    }

    public function create()
    {
        return view('opds.create');
    }

    public function update(Request $request, Opd $opd)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:opds,name,' . $opd->id,
        ]);

        $opd->update($validated);

        return redirect()->route('opds.index')->with('success', 'OPD updated successfully');
    }

    public function edit(Opd $opd)
    {
        $payload = [
            'opd' => $opd,
        ];

        return view('opds.edit', $payload);
    }

    public function destroy(Opd $opd)
    {
        $opd->delete();

        return redirect()->route('opds.index')->with('success', 'OPD deleted successfully');
    }
}


