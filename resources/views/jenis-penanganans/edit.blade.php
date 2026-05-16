@php
    $title = 'Edit Jenis Penanganan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    @php
        $selectedOpds = old('opd_ids', $jenisPenanganan->opds->pluck('id')->all());
    @endphp

    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="ti ti-notes" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Jenis Penanganan</h1>
                    <p class="mt-2 text-gray-600">Perbarui jenis penanganan dan OPD berwenang.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('jenis-penanganans.update', $jenisPenanganan->id) }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Jenis Penanganan</label>
                    <input id="name" name="name" value="{{ old('name', $jenisPenanganan->name) }}" placeholder="Masukkan nama jenis penanganan" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <p class="mb-3 block text-sm font-medium text-gray-700">OPD Berwenang</p>
                    <div class="max-h-72 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                        @foreach ($opds as $opd)
                            <label for="opd-{{ $opd->id }}" class="flex cursor-pointer items-start gap-3 rounded-md bg-white px-3 py-2">
                                <input
                                    id="opd-{{ $opd->id }}"
                                    type="checkbox"
                                    name="opd_ids[]"
                                    value="{{ $opd->id }}"
                                    @checked(in_array($opd->id, $selectedOpds))
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="block text-sm font-medium text-gray-900">{{ $opd->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('opd_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('opd_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('jenis-penanganans.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-indigo-600 text-sm font-medium text-white transition hover:bg-indigo-700">Update Jenis Penanganan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
