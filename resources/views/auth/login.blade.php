@php($title = 'Log in')
@extends('layouts.auth-blade')

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
                    placeholder="Username"
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
                    placeholder="........"
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

                @if ($canResetPassword)
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-sky-700 hover:text-sky-800">Lupa password?</a>
                @endif
            </div>

            <button type="submit" class="h-10 w-full rounded-lg bg-linear-to-r from-cyan-500 to-sky-500 text-sm font-semibold text-white shadow-md shadow-cyan-200 transition hover:from-cyan-600 hover:to-sky-600">
                Masuk
            </button>

            @if ($canRegister)
                <p class="text-center text-sm text-slate-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-sky-700 hover:text-sky-800">Daftar</a>
                </p>
            @endif
        </form>
    </div>
@endsection
