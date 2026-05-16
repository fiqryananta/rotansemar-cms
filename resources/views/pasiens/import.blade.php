@php($title = 'Import Pasien')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v12"></path>
                        <path d="m7 10 5 5 5-5"></path>
                        <path d="M5 21h14"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Import Pasien</h1>
                    <p class="mt-1 text-sm text-gray-500">Upload data pasien dalam format template Excel</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Upload File</h2>
                    <form method="POST" action="{{ route('pasiens.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">File Excel</label>
                            <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm" />
                            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 text-sm font-medium text-white transition hover:bg-cyan-700">Import Data</button>
                            <a href="{{ route('pasiens.import.template') }}" class="inline-flex h-10 items-center rounded-md border border-cyan-300 bg-cyan-50 px-4 text-sm font-medium text-cyan-700 transition hover:bg-cyan-100">Download Template</a>
                            <a href="{{ route('pasiens.index') }}" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali ke daftar pasien</a>
                        </div>
                    </form>

                    @if (!empty($importSummary))
                        <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                            <p class="font-medium">Ringkasan Import</p>
                            <p class="mt-1">Total baris: {{ $importSummary['total'] }}</p>
                            <p>Berhasil tambah: {{ $importSummary['inserted'] }}</p>
                            <p>Berhasil update: {{ $importSummary['updated'] }}</p>
                            <p>Dilewati: {{ $importSummary['skipped'] }}</p>
                        </div>
                    @endif

                    @if (!empty($importErrors))
                        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <p class="font-medium">Detail Baris Dilewati</p>
                            <ul class="mt-2 list-disc space-y-1 pl-4">
                                @foreach (array_slice($importErrors, 0, 20) as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                            @if (count($importErrors) > 20)
                                <p class="mt-2">Menampilkan 20 error pertama dari {{ count($importErrors) }} baris.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-3 text-lg font-semibold text-gray-900">Format Template</h2>
                    <p class="mb-3 text-sm text-gray-600">Gunakan nama kolom persis seperti berikut pada baris header:</p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        @foreach ($templateColumns as $column)
                            <li class="rounded bg-gray-50 px-2 py-1 font-mono text-xs">{{ $column }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-gray-500">Nilai gender harus salah satu: laki-laki atau perempuan.</p>
                    <p class="mt-1 text-xs text-gray-500">Format tanggal: YYYY-MM-DD (contoh: 2026-05-10).</p>
                </div>
            </div>
        </div>
    </div>
@endsection
