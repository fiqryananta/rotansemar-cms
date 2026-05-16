@php
    $title = 'Edit Jenis Kebutuhan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    @php
        $selectedJenisPenanganans = old('jenis_penanganan_ids', $jenisKebutuhan->jenisPenanganans->pluck('id')->all());
    @endphp

    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <i class="ti ti-table" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Jenis Kebutuhan</h1>
                    <p class="mt-2 text-gray-600">Perbarui jenis kebutuhan dan relasi jenis penanganan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('jenis-kebutuhans.update', $jenisKebutuhan->id) }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Kebutuhan</label>
                    <input id="name" name="name" value="{{ old('name', $jenisKebutuhan->name) }}" placeholder="Masukkan nama kebutuhan" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <p class="mb-3 block text-sm font-medium text-gray-700">Jenis Penanganan (bisa lebih dari 1)</p>
                    <div class="max-h-72 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                        @foreach ($jenisPenanganans as $jenis)
                            <label for="jenis-{{ $jenis->id }}" class="flex cursor-pointer items-start gap-3 rounded-md bg-white px-3 py-2">
                                <input
                                    id="jenis-{{ $jenis->id }}"
                                    type="checkbox"
                                    name="jenis_penanganan_ids[]"
                                    value="{{ $jenis->id }}"
                                    @checked(in_array($jenis->id, $selectedJenisPenanganans))
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                />
                                <span class="block text-sm font-medium text-gray-900">{{ $jenis->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_penanganan_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('jenis_penanganan_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('jenis-kebutuhans.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-amber-600 text-sm font-medium text-white transition hover:bg-amber-700">Update Jenis Kebutuhan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
