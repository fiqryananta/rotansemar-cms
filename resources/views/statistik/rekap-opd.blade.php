@php
    $title = 'Rekap OPD';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <i class="ti ti-table" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Rekap OPD</h1>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Rekap Pasien per OPD</h2>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">OPD</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Total Pasien</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pending</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Proses</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pending Bantuan</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Tidak Layak</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($opdSummary as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $row['total_pasien'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['pending_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['proses_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['pending_bantuan_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['tidak_layak_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['selesai_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada data pasien.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($opdSummary->isNotEmpty())
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-4 py-3 text-left font-semibold text-gray-900">Total</td>
                                        {{-- Total pasien tidak ikut dijumlahkan di footer; selnya dibiarkan
                                             kosong supaya kolom Pending/Proses dan seterusnya tetap lurus
                                             dengan header di atasnya. --}}
                                        <td class="px-4 py-3"></td>
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
        </div>
    </div>
@endsection
