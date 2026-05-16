@php($title = 'Tambah Role')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2 4 6v6c0 5 3.5 9.5 8 10 4.5-.5 8-5 8-10V6z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Role</h1>
                    <p class="mt-2 text-gray-600">Tambahkan role baru dan pilih permission.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('roles.store') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Administrator" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-3 block text-sm font-medium text-gray-700">Assign Permissions</label>
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-3">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2" for="perm-{{ $permission->id }}">
                                <input id="perm-{{ $permission->id }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, old('permissions', []))) class="h-4 w-4 rounded border-gray-300 text-amber-600" />
                                <span class="text-sm font-normal text-gray-700">{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('permissions')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('permissions.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('roles.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-amber-600 text-sm font-medium text-white transition hover:bg-amber-700">Simpan Role</button>
                </div>
            </form>
        </div>
    </div>
@endsection
