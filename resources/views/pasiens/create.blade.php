@php($title = 'Tambah Pasien')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="7" r="4"></circle>
                        <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Pasien</h1>
                    <p class="mt-1 text-sm text-gray-500">Isi data pasien dan kebutuhan penanganan.</p>
                </div>
            </div>

            @include('pasiens._form', [
                'formAction' => route('pasiens.store'),
                'formMethod' => 'POST',
                'submitLabel' => 'Simpan Pasien',
                'cancelHref' => route('pasiens.index'),
            ])
        </div>
    </div>
@endsection
