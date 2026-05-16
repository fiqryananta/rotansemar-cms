@php
    $title = 'Tambah Permission';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <i class="ti ti-lock" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Permission</h1>
                    <p class="mt-2 text-gray-600">Tambahkan permission baru dengan format dot notation.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('permissions.store') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Permission Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="users.view" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 font-mono text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-md border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-900">
                    Gunakan format: resource.action, contoh users.create atau posts.delete.
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('permissions.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-rose-600 text-sm font-medium text-white transition hover:bg-rose-700">Simpan Permission</button>
                </div>
            </form>
        </div>
    </div>
@endsection
