@php
    $title = 'Log in';

    // Tiap path berisi 2 periode identik sepanjang viewBox (0..2880), sehingga
    // animasi translateX(-50%) menghasilkan gelombang yang berulang mulus.
    $waves = [
        [
            'class' => 'fill-sky-200/40 animate-wave-slow',
            'path' => 'M0,80 C240,20 480,20 720,80 C960,140 1200,140 1440,80 C1680,20 1920,20 2160,80 C2400,140 2640,140 2880,80 L2880,220 L0,220 Z',
        ],
        [
            'class' => 'fill-sky-300/45 animate-wave-reverse',
            'path' => 'M0,120 C240,60 480,60 720,120 C960,180 1200,180 1440,120 C1680,60 1920,60 2160,120 C2400,180 2640,180 2880,120 L2880,220 L0,220 Z',
        ],
        [
            'class' => 'fill-cyan-400/45 animate-wave',
            'path' => 'M0,160 C240,105 480,105 720,160 C960,215 1200,215 1440,160 C1680,105 1920,105 2160,160 C2400,215 2640,215 2880,160 L2880,220 L0,220 Z',
        ],
    ];
@endphp
@extends('layouts.auth-blade')

@section('background')
    <div class="pointer-events-none absolute inset-x-0 bottom-0 overflow-hidden">
        <div class="relative h-32 sm:h-40 lg:h-48">
            @foreach ($waves as $wave)
                <svg
                    class="absolute bottom-0 left-0 h-full w-[200%] motion-reduce:animate-none {{ $wave['class'] }}"
                    viewBox="0 0 2880 220"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                >
                    <path d="{{ $wave['path'] }}" />
                </svg>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
    <div class="rounded-2xl border border-white/80 bg-white/90 p-6 shadow-[0_18px_44px_rgba(2,132,199,0.18)] backdrop-blur">
        <div class="mb-6 text-center">
            <div class="mx-auto flex h-20 items-center justify-center p-2">
                <img src="/images/logo-rotansemar.png" alt="Logo Rotan Semar" class="h-full w-full object-contain" />
            </div>

            <h1 class="mt-4 text-3xl font-bold text-slate-900">Selamat Datang</h1>
            <p class="mt-1 text-sm text-slate-600">Silahkan Login Menggunakan Akun Anda</p>
        </div>

        @if ($status)
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                {{ $status }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">Username</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="Masukkan Username"
                    class="mt-2 block h-10 w-full rounded-lg border border-slate-200 bg-white/90 px-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none"
                />
                @error('email')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan Password"
                    class="mt-2 block h-10 w-full rounded-lg border border-slate-200 bg-white/90 px-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sky-400 focus:outline-none"
                />
                @error('password')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600" for="remember">
                    <input id="remember" type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-sky-600" />
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="h-10 w-full rounded-lg bg-linear-to-r from-cyan-500 to-sky-500 text-sm font-semibold text-white shadow-md shadow-cyan-200 transition hover:from-cyan-600 hover:to-sky-600">
                Masuk
            </button>
        </form>
    </div>
@endsection
