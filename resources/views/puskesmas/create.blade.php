@php
    $title = 'Tambah Puskesmas';
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
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Puskesmas</h1>
                    <p class="mt-2 text-gray-600">Tambahkan puskesmas baru dan pilih kelurahan cakupan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('puskesmas.store') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Puskesmas</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama puskesmas" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <p class="mb-3 block text-sm font-medium text-gray-700">Kelurahan Cakupan</p>
                    <div class="max-h-72 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                        @foreach ($kelurahans as $kelurahan)
                            <label for="kelurahan-{{ $kelurahan->id }}" class="flex cursor-pointer items-start gap-3 rounded-md bg-white px-3 py-2">
                                <input
                                    id="kelurahan-{{ $kelurahan->id }}"
                                    type="checkbox"
                                    name="kelurahan_ids[]"
                                    value="{{ $kelurahan->id }}"
                                    @checked(in_array($kelurahan->id, old('kelurahan_ids', [])))
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                <span>
                                    <span class="block text-sm font-medium text-gray-900">{{ $kelurahan->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $kelurahan->kecamatan?->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('kelurahan_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('kelurahan_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('puskesmas.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-emerald-600 text-sm font-medium text-white transition hover:bg-emerald-700">Simpan Puskesmas</button>
                </div>
            </form>
        </div>
    </div>
@endsection
