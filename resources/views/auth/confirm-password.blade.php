@php($title = 'Confirm password')
@extends('layouts.auth-blade')

@section('content')
    <div class="rounded-2xl bg-white p-8 shadow-xl backdrop-blur-sm">
        <h1 class="text-2xl font-bold text-gray-900">Konfirmasi Password</h1>
        <p class="mt-2 text-sm text-gray-600">Area ini membutuhkan verifikasi password Anda.</p>

        <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    placeholder="........"
                    class="mt-2 block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none"
                />
                @error('password')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700">
                Confirm password
            </button>
        </form>
    </div>
@endsection
