@php($title = 'Tindak Lanjut')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 11h6"></path>
                        <path d="M9 15h6"></path>
                        <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tindak Lanjut Penanganan</h1>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <form id="filter-form" method="GET" action="{{ route('tindak-lanjut.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:max-w-4xl">
                            <div class="relative flex-1">
                                <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari pasien / NIK..." class="h-10 w-full rounded-md border border-gray-300 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:outline-none" />
                            </div>
                            <select id="status" name="status" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none">
                                <option value="">Semua Status</option>
                                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Menunggu Verifikasi</option>
                                <option value="proses" @selected(($filters['status'] ?? '') === 'proses')>Proses</option>
                                <option value="pending_bantuan" @selected(($filters['status'] ?? '') === 'pending_bantuan')>Pending Bantuan</option>
                                <option value="tidak_layak" @selected(($filters['status'] ?? '') === 'tidak_layak')>Tidak Layak</option>
                                <option value="selesai" @selected(($filters['status'] ?? '') === 'selesai')>Selesai</option>
                            </select>
                            <select id="opd_id" name="opd_id" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none">
                                <option value="">Semua OPD</option>
                                @foreach ($opds as $opd)
                                    <option value="{{ $opd->id }}" @selected(($filters['opd_id'] ?? '') == $opd->id)>{{ $opd->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="per_page" class="text-sm text-gray-600">Per halaman</label>
                            <select id="per_page" name="per_page" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none">
                                @foreach ([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected(($filters['per_page'] ?? 10) == $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex h-10 items-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-700">Cari</button>
                        </div>
                    </form>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Pasien</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Jenis Kebutuhan</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">OPD</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Jenis Penanganan</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Tindak Lanjut</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($kebutuhans as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $item->pasien?->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->pasien?->nik }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ $item->jenisKebutuhan?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $item->opd?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $item->jenisPenanganan?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', $item->verification_status)) }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $item->tindak_lanjuts_count }}x</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('tindak-lanjut.show', $item->id) }}" class="inline-flex h-9 items-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data penanganan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($links && $links->count() > 3)
                    <div class="mt-4 flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-medium">{{ $kebutuhans->firstItem() ?? 0 }}</span> -
                            <span class="font-medium">{{ $kebutuhans->lastItem() ?? 0 }}</span> dari
                            <span class="font-medium">{{ $kebutuhans->total() }}</span> data
                        </p>
                        <nav class="flex flex-wrap gap-1">
                            @foreach ($links as $link)
                                @if ($link['url'])
                                    <a href="{{ $link['url'] }}" class="rounded-md border px-3 py-1.5 text-sm transition-colors {{ $link['active'] ? 'border-indigo-500 bg-indigo-50 font-medium text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">{!! $link['label'] !!}</a>
                                @else
                                    <span class="rounded-md border border-gray-200 px-3 py-1.5 text-sm text-gray-400">{!! $link['label'] !!}</span>
                                @endif
                            @endforeach
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('search');
    const statusInput = document.getElementById('status');
    const opdInput = document.getElementById('opd_id');
    const perPageInput = document.getElementById('per_page');
    const form = document.getElementById('filter-form');
    if (!searchInput || !statusInput || !opdInput || !perPageInput || !form) return;
    let timeoutId;
    searchInput.addEventListener('input', function () {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(function () { form.submit(); }, 350);
    });
    statusInput.addEventListener('change', function () { form.submit(); });
    opdInput.addEventListener('change', function () { form.submit(); });
    perPageInput.addEventListener('change', function () { form.submit(); });
})();
</script>
@endpush
