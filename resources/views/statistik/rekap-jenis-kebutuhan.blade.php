@php
    $title = 'Rekap Jenis Kebutuhan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    @php
        // Kolomnya mengikuti master data, jadi jumlahnya tidak tetap di kode.
        $colspan = 2 + $jenisKebutuhans->count();
    @endphp

    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <i class="ti ti-category" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Rekap Jenis Kebutuhan</h1>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Rekap Pasien per Kecamatan</h2>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table id="rekap-jenis-kebutuhan" class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kecamatan</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Total Pasien</th>
                                    {{-- Nama jenis kebutuhan diambil dari master data, bukan ditulis di sini. --}}
                                    @foreach ($jenisKebutuhans as $jenis)
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700">{{ $jenis }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($kecamatanSummary as $row)
                                    <tr class="cursor-pointer hover:bg-gray-50" data-kecamatan-id="{{ $row['id'] }}" data-loaded="0">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            <button type="button" class="inline-flex items-center gap-2 text-left font-medium text-gray-900" data-kecamatan-toggle aria-expanded="false">
                                                <i class="ti ti-chevron-right text-gray-400 transition-transform duration-200" style="font-size:1rem;" data-chevron aria-hidden="true"></i>
                                                <span>{{ $row['name'] }}</span>
                                            </button>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $row['total_pasien'] }}</td>
                                        @foreach ($jenisKebutuhans as $id => $jenis)
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $row['counts'][$id] ?? 0 }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="px-4 py-10 text-center text-gray-400">Belum ada data pasien.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($kecamatanSummary->isNotEmpty())
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-4 py-3 text-left font-semibold text-gray-900">Total</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['total_pasien'] }}</td>
                                        @foreach ($jenisKebutuhans as $id => $jenis)
                                            <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['counts'][$id] ?? 0 }}</td>
                                        @endforeach
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    // Kolom baris rincian kelurahan mengikuti kolom tabelnya: nama, total, lalu
    // hitungan per jenis kebutuhan. Dipakai JS untuk menyusun sel baris anak.
    $drilldownKeys = array_merge(
        ['name', 'total_pasien'],
        $jenisKebutuhans->keys()->map(fn ($id) => 'counts.' . $id)->all()
    );
@endphp

@include('statistik._drilldown', [
    'tableId'    => 'rekap-jenis-kebutuhan',
    'endpoint'   => route('rekap-jenis-kebutuhan.kelurahan', ['kecamatan' => '__ID__']),
    'emptyText'  => 'Belum ada data kelurahan.',
    'columnKeys' => $drilldownKeys,
])
