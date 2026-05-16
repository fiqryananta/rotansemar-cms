@php($title = 'Detail Pasien')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Detail Pasien</h1>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('pasiens.index') }}" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
                    <a href="{{ route('pasiens.edit', $pasien->id) }}" class="inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 text-sm font-medium text-white transition hover:bg-cyan-700">Edit Pasien</a>
                </div>
            </div>

            <div class="space-y-6">
                <section class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Data Pasien - Data Diri</h2>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nama Pasien</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">NIK</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->nik) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tanggal Lahir</p><p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($pasien->birth_date)->format('d-m-Y') }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Jenis Kelamin</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->gender) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Faskes</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->faskes?->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Puskesmas</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->puskesmas?->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kecamatan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->kecamatan?->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kelurahan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->kelurahan?->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Koordinat</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->coordinates) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Berat Badan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->weight) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tinggi Badan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->height) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pernah Mendapatkan Bantuan</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->ever_received_assistance) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Bersedia Dibantu</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->willing_to_help) }}</p></div>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Alamat</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->address) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Alamat Baru</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->new_address) }}</p></div>
                    </div>
                </section>

                <section class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Data Pasien - Pekerjaan & Keluarga</h2>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Ekonomi</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->economic_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pekerjaan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->pekerjaan?->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nama Tempat Bekerja</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->workplace_name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Alamat Tempat Bekerja</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->workplace_address) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nama Kepala Keluarga</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->family_head_name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">NIK Kepala Keluarga</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->family_head_nik) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pekerjaan Kepala Keluarga</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->family_head_pekerjaan?->name) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Perkawinan Orang Tua</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->parent_marital_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pola Asuh</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->parenting_pattern) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Rentang Pendapatan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->family_income_range) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Responden</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->respondent) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Hubungan dengan Pasien</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->patient_relationship) }}</p></div>
                    </div>
                </section>

                <section class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Data Pasien - Riwayat Kesehatan</h2>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">TB SO/RO</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->tb_so_ro) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tanggal Mulai Pengobatan</p><p class="mt-1 text-sm text-gray-900">{{ \Illuminate\Support\Carbon::parse($pasien->treatment_start_date)->format('d/m/Y') }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tanggal Kunjungan</p><p class="mt-1 text-sm text-gray-900">{{ \Illuminate\Support\Carbon::parse($pasien->visit_date)->format('d/m/Y') }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tanggal Informasi</p><p class="mt-1 text-sm text-gray-900">{{ \Illuminate\Support\Carbon::parse($pasien->information_date)->format('d/m/Y') }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Pengobatan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->treatment_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Sumber Penularan</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->transmission_source) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Rencana Tindak Lanjut</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->follow_up_plan) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Kehamilan</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->pregnancy_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Komorbid</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->comorbid_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Perilaku Merokok</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->smoking_behavior) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Merokok Keluarga</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->family_smoking_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Imunisasi</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->immunization_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Gizi</p><p class="mt-1 text-sm text-gray-900">{{ $renderValue($pasien->nutritional_status) }}</p></div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kepemilikan JKN</p><p class="mt-1 text-sm text-gray-900">{{ $renderBoolean($pasien->jkn_ownership) }}</p></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
