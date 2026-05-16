@extends('layouts.admin-blade')

@section('title', 'Pengaturan Keamanan')

@section('content')
<div class="space-y-6">
    @include('settings._nav')

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h1 class="text-xl font-semibold text-gray-900">Ubah Password</h1>
        <p class="mt-1 text-sm text-gray-600">Gunakan password yang kuat dan unik.</p>

        <form method="POST" action="{{ route('user-password.update') }}" class="mt-6 grid gap-4 max-w-2xl">
            @csrf
            @method('PUT')

            <label class="block text-sm text-gray-700">
                <span class="mb-1 block font-medium">Password Saat Ini</span>
                <input
                    type="password"
                    name="current_password"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </label>

            <label class="block text-sm text-gray-700">
                <span class="mb-1 block font-medium">Password Baru</span>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </label>

            <label class="block text-sm text-gray-700">
                <span class="mb-1 block font-medium">Konfirmasi Password Baru</span>
                <input
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </label>

            <div>
                <button
                    type="submit"
                    class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700"
                >
                    Simpan Password
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Two Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-600">
            @if ($canManageTwoFactor)
                Status saat ini:
                <span class="font-medium {{ !empty($twoFactorEnabled) ? 'text-green-700' : 'text-amber-700' }}">
                    {{ !empty($twoFactorEnabled) ? 'Aktif' : 'Belum aktif' }}
                </span>
                @if (!empty($requiresConfirmation))
                    (perlu konfirmasi perangkat)
                @endif
            @else
                Fitur Two Factor Authentication tidak aktif di aplikasi.
            @endif
        </p>
    </section>
</div>
@endsection
