@php($title = 'Pasien')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21a8 8 0 1 0-16 0"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pasien</h1>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <form id="filter-form" method="GET" action="{{ route('pasiens.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1 sm:max-w-xs">
                            <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                            <input id="search" name="search" type="text" value="{{ $filters['search'] ?? '' }}" placeholder="Cari Pasien" class="h-10 w-full rounded-md border border-gray-300 pl-10 pr-3 text-sm focus:border-cyan-500 focus:outline-none" />
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="per_page" class="text-sm text-gray-600">Per halaman</label>
                            <select id="per_page" name="per_page" class="h-10 rounded-md border border-gray-300 px-3 text-sm focus:border-cyan-500 focus:outline-none">
                                @foreach ([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected(($filters['per_page'] ?? 10) == $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 text-sm font-medium text-white transition hover:bg-cyan-700">Cari</button>
                            @if (auth()->user()?->hasRole('admin'))
                                <a href="{{ route('pasiens.import.index') }}" class="inline-flex h-10 items-center rounded-md border border-cyan-300 bg-cyan-50 px-4 text-sm font-medium text-cyan-700 transition hover:bg-cyan-100">Import Pasien</a>
                            @endif
                            <a href="{{ route('pasiens.create') }}" class="inline-flex h-10 items-center rounded-md bg-gray-900 px-4 text-sm font-medium text-white transition hover:bg-gray-800">Tambah Pasien</a>
                        </div>
                    </form>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Identitas</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal Mulai Pengobatan</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Faskes</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kelurahan</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($pasiens as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $item->nik }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($item->treatment_start_date)->format('d-m-Y') }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $item->faskes?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $item->kelurahan?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('pasiens.show', $item->id) }}" class="inline-flex h-9 items-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Detail</a>
                                                <a href="{{ route('pasiens.edit', $item->id) }}" class="inline-flex h-9 items-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada data pasien.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($links && $links->count() > 3)
                    <div class="mt-4 flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-medium">{{ $pasiens->firstItem() ?? 0 }}</span> -
                            <span class="font-medium">{{ $pasiens->lastItem() ?? 0 }}</span> dari
                            <span class="font-medium">{{ $pasiens->total() }}</span> data
                        </p>
                        <nav class="flex flex-wrap gap-1">
                            @foreach ($links as $link)
                                @if ($link['url'])
                                    <a href="{{ $link['url'] }}" class="rounded-md border px-3 py-1.5 text-sm transition-colors {{ $link['active'] ? 'border-cyan-500 bg-cyan-50 font-medium text-cyan-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">{!! $link['label'] !!}</a>
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
