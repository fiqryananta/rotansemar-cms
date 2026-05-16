@php
    $title = 'Edit Kelurahan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <i class="ti ti-map-pin" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Kelurahan</h1>
                    <p class="mt-2 text-gray-600">Perbarui data kelurahan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('kelurahans.update', $kelurahan->id) }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')

                <div>
                    <label for="kecamatan_id" class="block text-sm font-medium text-gray-700">Kecamatan</label>
                    <select id="kecamatan_id" name="kecamatan_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih Kecamatan</option>
                        @foreach ($kecamatans as $kecamatan)
                            <option value="{{ $kecamatan->id }}" @selected(old('kecamatan_id', $kelurahan->kecamatan_id) == $kecamatan->id)>{{ $kecamatan->name }}</option>
                        @endforeach
                    </select>
                    @error('kecamatan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Kelurahan</label>
                    <input id="name" name="name" value="{{ old('name', $kelurahan->name) }}" placeholder="Masukkan nama kelurahan" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('kelurahans.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-violet-600 text-sm font-medium text-white transition hover:bg-violet-700">Update Kelurahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
