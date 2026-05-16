<?php

namespace App\Http\Controllers;

use App\Models\JenisKebutuhan;
use App\Models\JenisPenanganan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisKebutuhanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $jenisKebutuhans = JenisKebutuhan::query()
            ->with('jenisPenanganans:id,name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('jenisPenanganans', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'jenisKebutuhans' => $jenisKebutuhans,
            'links' => $jenisKebutuhans->linkCollection(),
            'filters' => [
                'search' => $search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('jenis-kebutuhans.index', $payload);
    }

    public function create()
    {
        $payload = [
            'jenisPenanganans' => JenisPenanganan::query()->orderBy('name')->get(['id', 'name']),
        ];

        return view('jenis-kebutuhans.create', $payload);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('jenis_kebutuhans', 'name')],
            'jenis_penanganan_ids' => ['required', 'array', 'min:1'],
            'jenis_penanganan_ids.*' => ['required', 'distinct', 'exists:jenis_penanganans,id'],
        ]);

        $jenisKebutuhan = JenisKebutuhan::create([
            'name' => $validated['name'],
        ]);

        $jenisKebutuhan->jenisPenanganans()->sync($validated['jenis_penanganan_ids']);

        return redirect()->route('jenis-kebutuhans.index')->with('success', 'Jenis kebutuhan created successfully');
    }

    public function edit(JenisKebutuhan $jenisKebutuhan)
    {
        $jenisKebutuhan->load('jenisPenanganans:id,name');

        $payload = [
            'jenisKebutuhan' => $jenisKebutuhan,
            'jenisPenanganans' => JenisPenanganan::query()->orderBy('name')->get(['id', 'name']),
        ];

        return view('jenis-kebutuhans.edit', $payload);
    }

    public function update(Request $request, JenisKebutuhan $jenisKebutuhan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('jenis_kebutuhans', 'name')->ignore($jenisKebutuhan->id)],
            'jenis_penanganan_ids' => ['required', 'array', 'min:1'],
            'jenis_penanganan_ids.*' => ['required', 'distinct', 'exists:jenis_penanganans,id'],
        ]);

        $jenisKebutuhan->update([
            'name' => $validated['name'],
        ]);

        $jenisKebutuhan->jenisPenanganans()->sync($validated['jenis_penanganan_ids']);

        return redirect()->route('jenis-kebutuhans.index')->with('success', 'Jenis kebutuhan updated successfully');
    }

    public function destroy(JenisKebutuhan $jenisKebutuhan)
    {
        $jenisKebutuhan->delete();

        return redirect()->route('jenis-kebutuhans.index')->with('success', 'Jenis kebutuhan deleted successfully');
    }
}


