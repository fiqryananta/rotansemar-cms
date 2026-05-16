<?php

namespace App\Http\Controllers;

use App\Models\JenisPenanganan;
use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisPenangananController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $jenisPenanganans = JenisPenanganan::query()
            ->with('opds:id,name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('opds', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'jenisPenanganans' => $jenisPenanganans,
            'links' => $jenisPenanganans->linkCollection(),
            'filters' => [
                'search' => $search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('jenis-penanganans.index', $payload);
    }

    public function create()
    {
        $payload = [
            'opds' => Opd::query()->orderBy('name')->get(['id', 'name']),
        ];

        return view('jenis-penanganans.create', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('jenis_penanganans', 'name')],
            'opd_ids' => ['required', 'array', 'min:1'],
            'opd_ids.*' => ['required', 'distinct', 'exists:opds,id'],
        ]);

        $jenisPenanganan = JenisPenanganan::create([
            'name' => $validated['name'],
        ]);

        $jenisPenanganan->opds()->sync($validated['opd_ids']);

        return redirect()->route('jenis-penanganans.index')->with('success', 'Jenis penanganan created successfully');
    }

    public function edit(JenisPenanganan $jenisPenanganan)
    {
        $jenisPenanganan->load('opds:id,name');

        $payload = [
            'jenisPenanganan' => $jenisPenanganan,
            'opds' => Opd::query()->orderBy('name')->get(['id', 'name']),
        ];

        return view('jenis-penanganans.edit', $payload);
    }

    public function update(Request $request, JenisPenanganan $jenisPenanganan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('jenis_penanganans', 'name')->ignore($jenisPenanganan->id)],
            'opd_ids' => ['required', 'array', 'min:1'],
            'opd_ids.*' => ['required', 'distinct', 'exists:opds,id'],
        ]);

        $jenisPenanganan->update([
            'name' => $validated['name'],
        ]);

        $jenisPenanganan->opds()->sync($validated['opd_ids']);

        return redirect()->route('jenis-penanganans.index')->with('success', 'Jenis penanganan updated successfully');
    }

    public function destroy(JenisPenanganan $jenisPenanganan)
    {
        $jenisPenanganan->delete();

        return redirect()->route('jenis-penanganans.index')->with('success', 'Jenis penanganan deleted successfully');
    }
}


