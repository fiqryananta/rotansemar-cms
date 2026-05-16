@php($title = 'Forgot password')
@extends('layouts.auth-blade')

@section('content')
    <div class="space-y-8">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg">
                <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="m3 7 9 6 9-6"></path>
                </svg>
            </div>
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Lupa Password?</h1>
            <p class="mt-2 text-sm text-gray-600">Masukkan email Anda untuk menerima link reset password.</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-xl backdrop-blur-sm">
            @if ($status)
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    {{ $status }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="user@example.com"
                        class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none"
                    />
                    @error('email')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700">
                    Kirim Link Reset Password
                </button>

                <div class="text-center text-sm text-gray-600">
                    Ingat password Anda?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">Kembali ke login</a>
                </div>
            </form>
        </div>
    </div>
@endsection
