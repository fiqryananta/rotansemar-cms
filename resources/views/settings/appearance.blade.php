@extends('layouts.admin-blade')

@section('title', 'Pengaturan Tampilan')

@section('content')
<div class="space-y-6">
    @include('settings._nav')

    <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h1 class="text-xl font-semibold text-gray-900">Pengaturan Tampilan</h1>
        <p class="mt-2 text-sm text-gray-600">
            Preferensi tampilan global disiapkan di halaman ini. Saat ini tema mengikuti pengaturan aplikasi.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-md border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-900">Tema Aktif</p>
                <p class="mt-1 text-sm text-gray-600">Mengikuti pengaturan default aplikasi.</p>
            </div>
            <div class="rounded-md border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-900">Mode Kontras</p>
                <p class="mt-1 text-sm text-gray-600">Akan ditambahkan pada iterasi berikutnya.</p>
            </div>
            <div class="rounded-md border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-900">Ukuran Teks</p>
                <p class="mt-1 text-sm text-gray-600">Akan tersedia setelah finalisasi desain aksesibilitas.</p>
            </div>
        </div>
    </section>
    <div>
        <a
            href="{{ route('profile.edit') }}"
            class="inline-flex rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700"
        >
            Kembali ke Pengaturan Profil
        </a>
    </div>
</div>
@endsection
