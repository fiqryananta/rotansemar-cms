@php($title = 'Dashboard')
@extends('layouts.admin-blade')

@section('content')
    <div class="bg-gray-50 rounded-2xl border border-gray-200/70 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="9" y="3" width="6" height="4" rx="1" />
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                        <path d="m9 14 2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($cards as $card)
                    <div class="rounded-lg border bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="inline-flex rounded-md px-2 py-1 text-xs font-semibold {{ $card['accent'] }}">{{ $card['label'] }}</span>
                            <span class="h-2.5 w-2.5 rounded-full bg-gray-300" aria-hidden="true"></span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900">Jumlah Pasien Berdasarkan Kebutuhan</h2>
                </div>

                <div class="px-4 pb-4">
                    <div class="overflow-x-auto rounded-md border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Jenis Kebutuhan</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Total Pasien</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pending</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Proses</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Pending Bantuan</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Tidak Layak</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($kebutuhanSummary as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row['jenis_kebutuhan_name'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $row['total_pasien'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['pending_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['proses_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['pending_bantuan_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['tidak_layak_count'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $row['selesai_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada data kebutuhan pasien.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
