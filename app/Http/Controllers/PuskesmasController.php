<?php

namespace App\Http\Controllers;

use App\Models\Kelurahan;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PuskesmasController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $puskesmas = Puskesmas::query()
            ->with('kelurahans:id,name')
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kelurahans', function ($subQuery) use ($request) {
                        $subQuery->where('name', 'like', '%' . $request->search . '%');
                    });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $kelurahans = Kelurahan::query()
            ->with('kecamatan:id,name')
            ->orderBy('name')
            ->get(['id', 'kecamatan_id', 'name']);

        $payload = [
            'puskesmas' => $puskesmas,
            'links' => $puskesmas->linkCollection(),
            'kelurahans' => $kelurahans,
            'filters' => [
                'search' => $request->search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('puskesmas.index', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:puskesmas,name',
            'kelurahan_ids' => 'required|array|min:1',
            'kelurahan_ids.*' => 'integer|exists:kelurahans,id|distinct',
        ]);

        $puskesmas = Puskesmas::create([
            'name' => $validated['name'],
        ]);

        $puskesmas->kelurahans()->sync($validated['kelurahan_ids']);

        return redirect()->route('puskesmas.index')->with('success', 'Puskesmas created successfully');
    }

    public function create()
    {
        $kelurahans = Kelurahan::query()
            ->with('kecamatan:id,name')
            ->orderBy('name')
            ->get(['id', 'kecamatan_id', 'name']);

        $payload = [
            'kelurahans' => $kelurahans,
        ];

        return view('puskesmas.create', $payload);
    }

    public function update(Request $request, Puskesmas $puskesmas)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('puskesmas', 'name')->ignore($puskesmas->id),
            ],
            'kelurahan_ids' => 'required|array|min:1',
            'kelurahan_ids.*' => 'integer|exists:kelurahans,id|distinct',
        ]);

        $puskesmas->update([
            'name' => $validated['name'],
        ]);

        $puskesmas->kelurahans()->sync($validated['kelurahan_ids']);

        return redirect()->route('puskesmas.index')->with('success', 'Puskesmas updated successfully');
    }

    public function edit(Puskesmas $puskesmas)
    {
        $kelurahans = Kelurahan::query()
            ->with('kecamatan:id,name')
            ->orderBy('name')
            ->get(['id', 'kecamatan_id', 'name']);

        $puskesmas->load('kelurahans:id,name,kecamatan_id');

        $payload = [
            'puskesmas' => $puskesmas,
            'kelurahans' => $kelurahans,
        ];

        return view('puskesmas.edit', $payload);
    }

    public function destroy(Puskesmas $puskesmas)
    {
        $puskesmas->kelurahans()->detach();
        $puskesmas->delete();

        return redirect()->route('puskesmas.index')->with('success', 'Puskesmas deleted successfully');
    }
}


