@php
    $title = 'Laporan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                    <i class="ti ti-chart-bar" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Laporan</h1>
                    <p class="mt-1 text-sm text-gray-500">Unduh laporan berdasarkan rentang tanggal.</p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">Pilih Rentang Tanggal</h2>

                <form id="laporan-form" method="GET" action="{{ route('laporan.export') }}" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="dari" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input id="dari" name="dari" type="date" value="{{ request('dari') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none" />
                        </div>

                        <div>
                            <label for="sampai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                            <input id="sampai" name="sampai" type="date" value="{{ request('sampai') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-cyan-500 focus:outline-none" />
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <p class="font-semibold">Periksa kembali tanggal yang dipilih.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 text-sm font-medium text-white transition hover:bg-cyan-700">Download Laporan</button>
                        <p class="text-sm text-gray-400">Pilih tanggal mulai dan selesai terlebih dahulu.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
