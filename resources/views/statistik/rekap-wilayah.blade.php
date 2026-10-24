@php
    $title = 'Rekap Wilayah';

    // Pasangan kolom status dan nilai verification_status-nya. Dipakai untuk
    // membuat angka di kolom itu bisa diklik dan membuka modal dengan filter
    // status yang sama.
    $statusColumns = [
        'pending_count'         => 'pending',
        'proses_count'          => 'proses',
        'pending_bantuan_count' => 'pending_bantuan',
        'tidak_layak_count'     => 'tidak_layak',
        'selesai_count'         => 'selesai',
    ];

    // Filter kelurahan yang sedang aktif ikut dibawa ke modal dan rincian
    // kelurahan, supaya angkanya sama dengan baris yang diklik.
    $filterQuery = (int) ($filters['kelurahan_id'] ?? 0) > 0
        ? ['kelurahan_id' => (int) $filters['kelurahan_id']]
        : [];
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <i class="ti ti-map-pin" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Rekap Wilayah</h1>
                </div>
            </div>

            {{-- Filter kecamatan dan kelurahan, langsung menyaring tabel di bawah. --}}
            @include('statistik._wilayah-filter', [
                'action' => route('rekap-wilayah.index'),
            ])

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Rekap Pasien per Kecamatan</h2>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table id="rekap-wilayah" class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kecamatan</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Total Pasien</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pending</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Proses</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pending Bantuan</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Tidak Layak</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($kecamatanSummary as $row)
                                    <tr class="cursor-pointer hover:bg-gray-50" data-kecamatan-id="{{ $row['id'] }}" data-loaded="0">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            <div class="flex items-center gap-2">
                                                {{-- Chevron tetap membuka rincian kelurahan seperti sebelumnya. --}}
                                                <button type="button" aria-label="Buka rincian kelurahan" data-kecamatan-toggle aria-expanded="false" class="inline-flex cursor-pointer items-center">
                                                    <i class="ti ti-chevron-right text-gray-400 transition-transform duration-200" style="font-size:1rem;" data-chevron aria-hidden="true"></i>
                                                </button>

                                                {{-- Nama kecamatan membuka modal daftar pasien, sama seperti nama OPD di Rekap OPD. --}}
                                                <button
                                                    type="button"
                                                    data-no-unify
                                                    data-rekap-detail
                                                    data-rekap-name="{{ $row['name'] }}"
                                                    data-rekap-url="{{ route('rekap-wilayah.detail', [$row['id']] + $filterQuery) }}"
                                                    class="cursor-pointer text-left font-medium text-cyan-700 underline"
                                                >{{ $row['name'] }}</button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $row['total_pasien'] }}</td>
                                        @foreach ($statusColumns as $countKey => $statusKey)
                                            <td class="px-4 py-3 text-center text-gray-700">
                                                {{-- Angka 0 tidak perlu diklik: tidak ada pasien untuk ditampilkan. --}}
                                                @if ($row[$countKey] > 0)
                                                    <button
                                                        type="button"
                                                        data-no-unify
                                                        data-rekap-detail
                                                        data-rekap-name="{{ $row['name'] }}"
                                                        data-rekap-url="{{ route('rekap-wilayah.detail', [$row['id']] + $filterQuery) }}"
                                                        data-filter-key="status"
                                                        data-filter-value="{{ $statusKey }}"
                                                        data-filter-label="Status: {{ $statusLabels[$statusKey] }}"
                                                        class="cursor-pointer font-medium text-cyan-700 underline"
                                                    >{{ $row[$countKey] }}</button>
                                                @else
                                                    {{ $row[$countKey] }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada data pasien.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($kecamatanSummary->isNotEmpty())
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-4 py-3 text-left font-semibold text-gray-900">Total</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['total_pasien'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['pending_count'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['proses_count'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['pending_bantuan_count'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['tidak_layak_count'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $totals['selesai_count'] }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal daftar pasien, dipakai bersama Rekap OPD. --}}
            @include('statistik._rekap-detail')
        </div>
    </div>
@endsection

@include('statistik._drilldown', [
    'tableId'    => 'rekap-wilayah',
    // Filter kelurahan ikut dikirim supaya baris anak dan modalnya memakai
    // himpunan data yang sama dengan baris induknya.
    'endpoint'   => route('rekap-wilayah.kelurahan', ['kecamatan' => '__ID__'] + $filterQuery),
    'emptyText'  => 'Belum ada data kelurahan.',
    'columnKeys' => [
        'name',
        'total_pasien',
        'pending_count',
        'proses_count',
        'pending_bantuan_count',
        'tidak_layak_count',
        'selesai_count',
    ],
    // Baris kelurahan ikut jadi pemicu modal, sama seperti baris kecamatan.
    'detailUrl'      => route('rekap-wilayah.kelurahan-detail', ['kelurahan' => '__ID__'] + $filterQuery),
    'filterColumns'  => collect($statusColumns)->map(fn ($statusKey) => [
        'key'   => 'status',
        'value' => $statusKey,
        'label' => 'Status: ' . $statusLabels[$statusKey],
    ])->all(),
])
