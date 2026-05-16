@php
    $title = 'Reset password';
@endphp
@extends('layouts.auth-blade')

@section('content')
    <div class="space-y-8">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg">
                <i class="ti ti-lock-question text-white" style="font-size:2rem;" aria-hidden="true"></i>
            </div>
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Atur Ulang Password</h1>
            <p class="mt-2 text-sm text-gray-600">Masukkan password baru untuk akun Anda.</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-xl backdrop-blur-sm">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}" />

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        readonly
                        class="mt-2 block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-gray-500"
                    />
                    @error('email')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password Baru</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="........"
                        class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none"
                    />
                    @error('password')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="........"
                        class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none"
                    />
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700">
                    Atur Ulang Password
                </button>
            </form>
        </div>
    </div>
@endsection
