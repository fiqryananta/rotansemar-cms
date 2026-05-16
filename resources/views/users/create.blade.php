@php
    $title = 'Tambah User';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <i class="ti ti-user-plus" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah User</h1>
                </div>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" type="password" name="password" placeholder="Minimal 8 karakter" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password" class="mt-1 h-10 w-full rounded-md border border-gray-300 px-3 text-sm" />
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role_id" name="role_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="scope-opd" class="hidden">
                    <label for="opd_id" class="block text-sm font-medium text-gray-700">OPD</label>
                    <select id="opd_id" name="opd_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih OPD</option>
                        @foreach ($opds as $item)
                            <option value="{{ $item->id }}" @selected((string) old('opd_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('opd_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="scope-faskes" class="hidden">
                    <label for="faskes_id" class="block text-sm font-medium text-gray-700">Faskes</label>
                    <select id="faskes_id" name="faskes_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih Faskes</option>
                        @foreach ($faskes as $item)
                            <option value="{{ $item->id }}" @selected((string) old('faskes_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('faskes_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="scope-puskesmas" class="hidden">
                    <label for="puskesmas_id" class="block text-sm font-medium text-gray-700">Puskesmas</label>
                    <select id="puskesmas_id" name="puskesmas_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih Puskesmas</option>
                        @foreach ($puskesmas as $item)
                            <option value="{{ $item->id }}" @selected((string) old('puskesmas_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('puskesmas_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="scope-kecamatan" class="hidden">
                    <label for="kecamatan_id" class="block text-sm font-medium text-gray-700">Kecamatan</label>
                    <select id="kecamatan_id" name="kecamatan_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih Kecamatan</option>
                        @foreach ($kecamatans as $item)
                            <option value="{{ $item->id }}" @selected((string) old('kecamatan_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('kecamatan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="scope-kelurahan" class="hidden">
                    <label for="kelurahan_id" class="block text-sm font-medium text-gray-700">Kelurahan</label>
                    <select id="kelurahan_id" name="kelurahan_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" disabled>
                        <option value="">Pilih Kecamatan dulu</option>
                    </select>
                    @error('kelurahan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <p id="role-hint-empty" class="hidden rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                    Pilih role terlebih dahulu untuk menentukan relasi unit.
                </p>

                <p id="role-hint-admin" class="hidden rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                    Role Admin memiliki akses seluruh modul tanpa relasi unit khusus.
                </p>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('users.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-sky-600 text-sm font-medium text-white transition hover:bg-sky-700">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const roles = @json($roles->map(fn ($role) => ['id' => $role->id, 'name' => strtolower($role->name)])->values());
            const kelurahans = @json($kelurahans->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'kecamatan_id' => $item->kecamatan_id])->values());

            const roleSelect = document.getElementById('role_id');
            const kecamatanSelect = document.getElementById('kecamatan_id');
            const kelurahanSelect = document.getElementById('kelurahan_id');

            const scopeOpd = document.getElementById('scope-opd');
            const scopeFaskes = document.getElementById('scope-faskes');
            const scopePuskesmas = document.getElementById('scope-puskesmas');
            const scopeKecamatan = document.getElementById('scope-kecamatan');
            const scopeKelurahan = document.getElementById('scope-kelurahan');
            const hintEmpty = document.getElementById('role-hint-empty');
            const hintAdmin = document.getElementById('role-hint-admin');

            const opdSelect = document.getElementById('opd_id');
            const faskesSelect = document.getElementById('faskes_id');
            const puskesmasSelect = document.getElementById('puskesmas_id');

            function selectedRoleName() {
                const roleId = Number(roleSelect.value);
                const role = roles.find((item) => item.id === roleId);
                return role ? role.name : '';
            }

            function resetScopedFields() {
                opdSelect.value = '';
                faskesSelect.value = '';
                puskesmasSelect.value = '';
                kecamatanSelect.value = '';
                kelurahanSelect.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                kelurahanSelect.disabled = true;
            }

            function toggleScopes(reset = false) {
                const roleName = selectedRoleName();

                if (reset) {
                    resetScopedFields();
                }

                scopeOpd.classList.toggle('hidden', roleName !== 'opd');
                scopeFaskes.classList.toggle('hidden', roleName !== 'faskes');
                scopePuskesmas.classList.toggle('hidden', roleName !== 'puskesmas');
                scopeKecamatan.classList.toggle('hidden', !(roleName === 'kecamatan' || roleName === 'kelurahan'));
                scopeKelurahan.classList.toggle('hidden', roleName !== 'kelurahan');

                hintEmpty.classList.toggle('hidden', roleName !== '');
                hintAdmin.classList.toggle('hidden', roleName !== 'admin');

                if (roleName === 'kelurahan') {
                    hydrateKelurahanOptions();
                }
            }

            function hydrateKelurahanOptions() {
                const kecamatanId = Number(kecamatanSelect.value);
                const selectedOld = '{{ old('kelurahan_id') }}';

                if (!kecamatanId) {
                    kelurahanSelect.innerHTML = '<option value="">Pilih Kecamatan dulu</option>';
                    kelurahanSelect.disabled = true;
                    return;
                }

                const options = kelurahans.filter((item) => item.kecamatan_id === kecamatanId);

                kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
                options.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.name;
                    if (selectedOld && String(item.id) === String(selectedOld)) {
                        option.selected = true;
                    }
                    kelurahanSelect.appendChild(option);
                });

                kelurahanSelect.disabled = false;
            }

            roleSelect.addEventListener('change', function () {
                toggleScopes(true);
            });

            kecamatanSelect.addEventListener('change', function () {
                kelurahanSelect.value = '';
                hydrateKelurahanOptions();
            });

            toggleScopes(false);
        })();
    </script>
@endpush
