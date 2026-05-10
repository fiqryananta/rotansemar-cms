<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\PasienKebutuhan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TindakLanjutController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = $this->applyTindakLanjutScope(PasienKebutuhan::query(), $request->user())
            ->with([
                'pasien:id,name,nik',
                'opd:id,name',
                'jenisPenanganan:id,name',
                'jenisKebutuhan:id,name',
            ])
            ->withCount('tindakLanjuts')
            ->when($request->filled('status'), fn ($q) => $q->where('verification_status', $request->status))
            ->when($request->filled('opd_id'), fn ($q) => $q->where('opd_id', $request->opd_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('pasien', fn ($pq) => $pq->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nik', 'like', '%' . $request->search . '%'));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/TindakLanjut/Index', [
            'kebutuhans' => $query,
            'opds' => $this->allowedOpds($request->user()),
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
                'opd_id' => $request->opd_id ? (int) $request->opd_id : '',
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(PasienKebutuhan $pasienKebutuhan)
    {
        abort_unless($this->canAccessTindakLanjut(request()->user(), $pasienKebutuhan), 403);

        $pasienKebutuhan->load([
            'pasien:id,name,nik,address,catatan_kebutuhan',
            'opd:id,name',
            'jenisPenanganan:id,name',
            'jenisKebutuhan:id,name',
            'tindakLanjuts' => fn ($q) => $q->with(['user:id,name,email', 'fotos']),
        ]);

        return Inertia::render('Admin/TindakLanjut/Show', [
            'kebutuhan' => $pasienKebutuhan,
        ]);
    }

    public function verifikasi(Request $request, PasienKebutuhan $pasienKebutuhan)
    {
        abort_unless($this->canAccessTindakLanjut($request->user(), $pasienKebutuhan), 403);

        $request->validate([
            'status' => ['required', Rule::in(['proses', 'pending_bantuan', 'tidak_layak'])],
        ]);

        $pasienKebutuhan->update(['verification_status' => $request->status]);

        return redirect()->route('tindak-lanjut.show', $pasienKebutuhan)
            ->with('success', 'Status verifikasi berhasil diperbarui.');
    }

    public function tambahRiwayat(Request $request, PasienKebutuhan $pasienKebutuhan)
    {
        abort_unless($this->canAccessTindakLanjut($request->user(), $pasienKebutuhan), 403);

        if (!in_array($pasienKebutuhan->verification_status, ['proses', 'pending_bantuan'], true)) {
            return back()->with('error', 'Tidak dapat menambahkan tindak lanjut pada status ini.');
        }

        $request->validate([
            'keterangan' => ['required', 'string', 'max:2000'],
            'fotos' => ['required', 'array', 'min:1'],
            'fotos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'fotos.required' => 'Minimal 1 foto wajib diunggah.',
            'fotos.min' => 'Minimal 1 foto wajib diunggah.',
            'fotos.*.image' => 'File harus berupa gambar.',
            'fotos.*.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'fotos.*.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $tindakLanjut = $pasienKebutuhan->tindakLanjuts()->create([
            'keterangan' => $request->keterangan,
            'user_id' => $request->user()->id,
        ]);

        foreach ($request->file('fotos') as $foto) {
            $path = $foto->store('tindak-lanjut/' . $tindakLanjut->id, 'public');
            $tindakLanjut->fotos()->create(['path' => $path]);
        }

        return redirect()->route('tindak-lanjut.show', $pasienKebutuhan)
            ->with('success', 'Tindak lanjut berhasil ditambahkan.');
    }

    public function selesai(PasienKebutuhan $pasienKebutuhan)
    {
        abort_unless($this->canAccessTindakLanjut(request()->user(), $pasienKebutuhan), 403);

        if (!in_array($pasienKebutuhan->verification_status, ['proses', 'pending_bantuan'], true)) {
            return back()->with('error', 'Status tidak valid untuk diselesaikan.');
        }

        if ($pasienKebutuhan->tindakLanjuts()->count() === 0) {
            return back()->with('error', 'Harus ada minimal satu riwayat tindak lanjut sebelum dapat diselesaikan.');
        }

        $pasienKebutuhan->update(['verification_status' => 'selesai']);

        return redirect()->route('tindak-lanjut.show', $pasienKebutuhan)
            ->with('success', 'Penanganan telah diselesaikan.');
    }

    private function applyTindakLanjutScope($query, $user)
    {
        if (!$user) {
            return $query;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return $query;
        }

        if ($roles->contains('opd') && $user->opd_id) {
            return $query->where('opd_id', $user->opd_id);
        }

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            return $query->whereHas('pasien', fn ($pq) => $pq->where('puskesmas_id', $user->puskesmas_id));
        }

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            return $query->whereHas('pasien', fn ($pq) => $pq->where('kecamatan_id', $user->kecamatan_id));
        }

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            return $query->whereHas('pasien', fn ($pq) => $pq->where('kelurahan_id', $user->kelurahan_id));
        }

        return $query->whereRaw('1 = 0');
    }

    private function canAccessTindakLanjut($user, PasienKebutuhan $pasienKebutuhan): bool
    {
        if (!$user) {
            return false;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return true;
        }

        if ($roles->contains('opd')) {
            return (int) $user->opd_id === (int) $pasienKebutuhan->opd_id;
        }

        $pasienKebutuhan->loadMissing('pasien:id,puskesmas_id,kecamatan_id,kelurahan_id');

        if ($roles->contains('puskesmas')) {
            return (int) $user->puskesmas_id === (int) $pasienKebutuhan->pasien?->puskesmas_id;
        }

        if ($roles->contains('kecamatan')) {
            return (int) $user->kecamatan_id === (int) $pasienKebutuhan->pasien?->kecamatan_id;
        }

        if ($roles->contains('kelurahan')) {
            return (int) $user->kelurahan_id === (int) $pasienKebutuhan->pasien?->kelurahan_id;
        }

        return false;
    }

    private function allowedOpds($user)
    {
        if (!$user) {
            return [];
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return Opd::query()->orderBy('name')->get(['id', 'name']);
        }

        if ($roles->contains('opd') && $user->opd_id) {
            return Opd::query()->whereKey($user->opd_id)->get(['id', 'name']);
        }

        return Opd::query()->orderBy('name')->get(['id', 'name']);
    }
}
