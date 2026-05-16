@php($title = 'Pengambilan Obat')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2v20"></path>
                        <path d="M2 12h20"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pengambilan Obat</h1>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ session('error') }}</div>
            @endif

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <form id="filter-form" method="GET" action="{{ route('medication-pickups.index') }}" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:max-w-3xl">
                            <div class="relative flex-1">
                                <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari pasien atau faskes" class="h-10 w-full rounded-md border border-gray-300 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none" />
                            </div>
                            <select id="status" name="status" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="per_page" class="text-sm text-gray-600">Per halaman</label>
                            <select id="per_page" name="per_page" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none">
                                @foreach ([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected(($filters['per_page'] ?? 10) == $size)>{{ $size }}</option>
                                @endforeach
                            </select>

                            <a href="{{ route('medication-pickups.create') }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                                Tambah
                            </a>
                        </div>
                    </form>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Pasien</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Faskes / Puskesmas</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Jadwal</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($pickups as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            <div>
                                                <p>{{ $item->pasien?->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $item->pasien?->nik }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if ($item->faskes)
                                                <p class="font-medium">{{ $item->faskes->name }}</p>
                                            @endif
                                            @if ($item->puskesmas)
                                                <p class="text-gray-600">{{ $item->puskesmas->name }}</p>
                                            @endif
                                            @if (!$item->faskes && !$item->puskesmas)
                                                <p class="text-gray-500">-</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->scheduled_date }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $statuses[$item->status] ?? $item->status }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('medication-pickups.edit', $item->id) }}" class="inline-flex h-9 items-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Edit</a>
                                                <form method="POST" action="{{ route('medication-pickups.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus data pengambilan obat ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-9 items-center rounded-md bg-rose-50 px-3 text-sm font-medium text-rose-700 transition hover:bg-rose-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada data pengambilan obat</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($links && $links->count() > 3)
                    <div class="mt-4 flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-medium">{{ $pickups->firstItem() ?? 0 }}</span> -
                            <span class="font-medium">{{ $pickups->lastItem() ?? 0 }}</span> dari
                            <span class="font-medium">{{ $pickups->total() }}</span> data
                        </p>
                        <nav class="flex flex-wrap gap-1">
                            @foreach ($links as $link)
                                @if ($link['url'])
                                    <a href="{{ $link['url'] }}" class="rounded-md border px-3 py-1.5 text-sm transition-colors {{ $link['active'] ? 'border-blue-500 bg-blue-50 font-medium text-blue-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">{!! $link['label'] !!}</a>
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
    const perPageInput = document.getElementById('per_page');
    const form = document.getElementById('filter-form');
    if (!searchInput || !statusInput || !perPageInput || !form) return;
    let timeoutId;
    searchInput.addEventListener('input', function () {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(function () { form.submit(); }, 350);
    });
    statusInput.addEventListener('change', function () { form.submit(); });
    perPageInput.addEventListener('change', function () { form.submit(); });
})();
</script>
@endpush
