@php
    $title = 'Puskesmas';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="ti ti-building-hospital" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Puskesmas</h1>
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
                    <form id="filter-form" method="GET" action="{{ route('puskesmas.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1 sm:max-w-xs">
                            <i class="ti ti-search pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-400" style="font-size:1rem;" aria-hidden="true"></i>
                            <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari Puskesmas" class="h-10 w-full rounded-md border border-gray-300 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none" />
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="per_page" class="text-sm text-gray-600">Per halaman</label>
                            <select id="per_page" name="per_page" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none">
                                @foreach ([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected(($filters['per_page'] ?? 10) == $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex h-10 items-center rounded-md bg-emerald-600 px-4 text-sm font-medium text-white transition hover:bg-emerald-700">Cari</button>

                            <a href="{{ route('puskesmas.create') }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-emerald-600 px-4 text-sm font-medium text-white transition hover:bg-emerald-700">
                                <i class="ti ti-plus" style="font-size:1rem;"></i>
                                Tambah Puskesmas
                            </a>
                        </div>
                    </form>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Puskesmas</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kelurahan</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($puskesmas as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->name }}</td>
                                        <td class="px-4 py-3">
                                            @if ($item->kelurahans->count() > 0)
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($item->kelurahans as $kelurahan)
                                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $kelurahan->name }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">Belum ada kelurahan</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('puskesmas.edit', $item->id) }}" class="inline-flex h-9 items-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Edit</a>
                                                <form method="POST" action="{{ route('puskesmas.destroy', $item->id) }}" data-confirm="Yakin ingin menghapus Puskesmas {{ addslashes($item->name) }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-9 items-center rounded-md bg-rose-50 px-3 text-sm font-medium text-rose-700 transition hover:bg-rose-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum Ada Puskesmas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($links && $links->count() > 3)
                    <div class="mt-4 flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-medium">{{ $puskesmas->firstItem() ?? 0 }}</span> -
                            <span class="font-medium">{{ $puskesmas->lastItem() ?? 0 }}</span> dari
                            <span class="font-medium">{{ $puskesmas->total() }}</span> data
                        </p>
                        <nav class="flex flex-wrap gap-1">
                            @foreach ($links as $link)
                                @if ($link['url'])
                                    <a href="{{ $link['url'] }}" class="rounded-md border px-3 py-1.5 text-sm transition-colors {{ $link['active'] ? 'border-emerald-500 bg-emerald-50 font-medium text-emerald-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">{!! $link['label'] !!}</a>
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
    const perPageInput = document.getElementById('per_page');
    const form = document.getElementById('filter-form');
    if (!searchInput || !perPageInput || !form) return;
    let timeoutId;
    searchInput.addEventListener('input', function () {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(function () { form.submit(); }, 350);
    });
    perPageInput.addEventListener('change', function () { form.submit(); });
})();
</script>
@endpush
