@php
    $title = 'Email verification';
@endphp
@extends('layouts.auth-blade')

@section('content')
    <div class="rounded-2xl bg-white p-8 text-center shadow-xl backdrop-blur-sm">
        <h1 class="text-2xl font-bold text-gray-900">Verifikasi Email</h1>
        <p class="mt-2 text-sm text-gray-600">
            Silakan verifikasi email Anda dengan klik tautan yang kami kirim.
        </p>

        @if ($status === 'verification-link-sent')
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                Link verifikasi baru sudah dikirim ke email Anda.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
            @csrf
            <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-800">
                Log out
            </button>
        </form>
    </div>
@endsection
