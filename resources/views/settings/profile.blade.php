@extends('layouts.admin-blade')

@section('title', 'Pengaturan Profil')

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
        <h1 class="text-xl font-semibold text-gray-900">Profil Akun</h1>
        <p class="mt-1 text-sm text-gray-600">Perbarui nama dan email akun Anda.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="mt-6 grid gap-4 md:grid-cols-2">
            @csrf
            @method('PATCH')

            <label class="block text-sm text-gray-700 md:col-span-1">
                <span class="mb-1 block font-medium">Nama</span>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', auth()->user()->name) }}"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </label>

            <label class="block text-sm text-gray-700 md:col-span-1">
                <span class="mb-1 block font-medium">Email</span>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', auth()->user()->email) }}"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </label>

            <div class="md:col-span-2">
                <button
                    type="submit"
                    class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700"
                >
                    Simpan Profil
                </button>
            </div>
        </form>

        @if ($mustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <p>Email Anda belum terverifikasi.</p>
                <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="font-medium underline">Kirim ulang email verifikasi</button>
                </form>
                @if ($status === 'verification-link-sent')
                    <p class="mt-2 text-green-700">Link verifikasi baru telah dikirim.</p>
                @endif
            </div>
        @endif
    </section>

    <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h2 class="text-xl font-semibold text-red-700">Hapus Akun</h2>
        <p class="mt-1 text-sm text-gray-600">Setelah dihapus, semua data akun akan hilang permanen.</p>

        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-4">
            @csrf
            @method('DELETE')

            <label class="block text-sm text-gray-700 max-w-md">
                <span class="mb-1 block font-medium">Konfirmasi password</span>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                >
            </label>

            <button
                type="submit"
                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                data-confirm="Yakin ingin menghapus akun ini?"
            >
                Hapus Akun
            </button>
        </form>
    </section>
</div>
@endsection
