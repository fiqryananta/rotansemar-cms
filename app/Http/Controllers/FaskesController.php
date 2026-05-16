<?php

namespace App\Http\Controllers;

use App\Models\Faskes;
use Illuminate\Http\Request;

class FaskesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $faskes = Faskes::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'faskes' => $faskes,
            'links' => $faskes->linkCollection(),
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('faskes.index', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faskes,name',
        ]);

        Faskes::create($validated);

        return redirect()->route('faskes.index')->with('success', 'Faskes created successfully');
    }

    public function create()
    {
        return view('faskes.create');
    }

    public function update(Request $request, Faskes $faskes)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faskes,name,' . $faskes->id,
        ]);

        $faskes->update($validated);

        return redirect()->route('faskes.index')->with('success', 'Faskes updated successfully');
    }

    public function edit(Faskes $faskes)
    {
        $payload = [
            'faskes' => $faskes,
        ];

        return view('faskes.edit', $payload);
    }

    public function destroy(Faskes $faskes)
    {
        $faskes->delete();

        return redirect()->route('faskes.index')->with('success', 'Faskes deleted successfully');
    }
}


