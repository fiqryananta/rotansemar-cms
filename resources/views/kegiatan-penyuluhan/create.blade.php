@php($title = 'Tambah Kegiatan Penyuluhan')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 11l18-6v14L3 13z"></path>
                        <path d="M9 15v4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Kegiatan Penyuluhan</h1>
                    <p class="mt-2 text-gray-600">Isi detail kegiatan dan unggah foto kegiatan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('kegiatan-penyuluhan.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="nama_kegiatan">Nama Kegiatan</label>
                        <input id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('nama_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="tanggal_kegiatan">Tanggal Kegiatan</label>
                        <input id="tanggal_kegiatan" name="tanggal_kegiatan" type="date" value="{{ old('tanggal_kegiatan') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('tanggal_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="lokasi_kegiatan">Lokasi Kegiatan</label>
                        <input id="lokasi_kegiatan" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('lokasi_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="sasaran">Sasaran</label>
                        <input id="sasaran" name="sasaran" value="{{ old('sasaran') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('sasaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="jumlah_sasaran">Jumlah Sasaran</label>
                        <input id="jumlah_sasaran" name="jumlah_sasaran" type="number" min="1" value="{{ old('jumlah_sasaran') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('jumlah_sasaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="koordinat_lokasi">Koordinat Lokasi</label>
                        <input id="koordinat_lokasi" name="koordinat_lokasi" value="{{ old('koordinat_lokasi') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" placeholder="Lat, Long" />
                        <p class="mt-1 text-xs text-gray-500">Boleh dikosongkan.</p>
                        @error('koordinat_lokasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="uraian_kegiatan">Uraian Kegiatan</label>
                    <textarea id="uraian_kegiatan" name="uraian_kegiatan" rows="4" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('uraian_kegiatan') }}</textarea>
                    @error('uraian_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="foto_kegiatan">Foto Kegiatan</label>
                    <input id="foto_kegiatan" name="foto_kegiatan[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="mt-1 block w-full text-sm" />
                    <p class="mt-1 text-xs text-gray-500">Bisa pilih lebih dari satu foto.</p>
                    @error('foto_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('foto_kegiatan.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('kegiatan-penyuluhan.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-cyan-600 text-sm font-medium text-white transition hover:bg-cyan-700">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
