@php
    $title = 'Edit Pasien';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                        <i class="ti ti-user" style="font-size:1.5rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Pasien</h1>
                    </div>
                </div>
                <a href="{{ route('pasiens.show', $pasien->id) }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">Batal</a>
            </div>

            @include('pasiens._form', [
                'pasien' => $pasien,
                'formAction' => route('pasiens.update', $pasien->id),
                'formMethod' => 'PUT',
                'submitLabel' => 'Simpan Perubahan',
                'cancelHref' => route('pasiens.show', $pasien->id),
            ])
        </div>
    </div>
@endsection
