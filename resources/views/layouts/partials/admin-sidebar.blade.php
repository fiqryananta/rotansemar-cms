@php
    $user = auth()->user();

    $sections = [
        'Navigation' => [
            ['title' => 'Dashboard', 'href' => '/dashboard', 'permission' => 'dashboard.view', 'active' => request()->is('dashboard'), 'icon' => 'dashboard'],
            ['title' => 'Pasien', 'href' => '/pasiens', 'permission' => 'pasiens.view', 'active' => request()->is('pasiens*'), 'icon' => 'pasien'],
            ['title' => 'Tindak Lanjut', 'href' => '/tindak-lanjut', 'permission' => 'tindak-lanjut.view', 'active' => request()->is('tindak-lanjut*'), 'icon' => 'tindak_lanjut'],
            ['title' => 'Pengambilan Obat', 'href' => '/medication-pickups', 'permission' => 'medication-pickups.view', 'active' => request()->is('medication-pickups*'), 'icon' => 'pengambilan_obat'],
        ],
        'Laporan' => [
            ['title' => 'Laporan', 'href' => '/laporan', 'permission' => 'laporan.view', 'active' => request()->is('laporan*'), 'icon' => 'laporan'],
        ],
        'Kegiatan' => [
            ['title' => 'Kegiatan Penyuluhan', 'href' => '/kegiatan-penyuluhan', 'permission' => 'kegiatan-penyuluhan.view', 'active' => request()->is('kegiatan-penyuluhan*'), 'icon' => 'kegiatan_penyuluhan'],
        ],
        'Manajemen' => [
            ['title' => 'Users', 'href' => '/users', 'permission' => 'users.view', 'active' => request()->is('users*'), 'icon' => 'users'],
            ['title' => 'Role', 'href' => '/roles', 'permission' => 'roles.view', 'active' => request()->is('roles*'), 'icon' => 'roles'],
            ['title' => 'Permission', 'href' => '/permissions', 'permission' => 'permissions.view', 'active' => request()->is('permissions*'), 'icon' => 'permissions'],
        ],
        'Data Master' => [
            ['title' => 'OPD', 'href' => '/opds', 'permission' => 'opds.view', 'active' => request()->is('opds*'), 'icon' => 'opd'],
            ['title' => 'Puskesmas', 'href' => '/puskesmas', 'permission' => 'puskesmas.view', 'active' => request()->is('puskesmas*'), 'icon' => 'fasilitas'],
            ['title' => 'Faskes', 'href' => '/faskes', 'permission' => 'faskes.view', 'active' => request()->is('faskes*'), 'icon' => 'fasilitas'],
            ['title' => 'Kecamatan', 'href' => '/kecamatans', 'permission' => 'kecamatans.view', 'active' => request()->is('kecamatans*'), 'icon' => 'fasilitas'],
            ['title' => 'Kelurahan', 'href' => '/kelurahans', 'permission' => 'kelurahans.view', 'active' => request()->is('kelurahans*'), 'icon' => 'kelurahan'],
            ['title' => 'Pekerjaan', 'href' => '/pekerjaan', 'permission' => 'pekerjaan.view', 'active' => request()->is('pekerjaan*'), 'icon' => 'pekerjaan'],
            ['title' => 'Jenis Penanganan', 'href' => '/jenis-penanganans', 'permission' => 'jenis-penanganans.view', 'active' => request()->is('jenis-penanganans*'), 'icon' => 'jenis_penanganan'],
            ['title' => 'Jenis Kebutuhan', 'href' => '/jenis-kebutuhans', 'permission' => 'jenis-kebutuhans.view', 'active' => request()->is('jenis-kebutuhans*'), 'icon' => 'jenis_kebutuhan'],
        ],
    ];
@endphp

<aside id="admin-sidebar" class="w-full border border-white/65 bg-white/55 p-3 shadow-[0_8px_20px_rgba(15,23,42,0.05)] backdrop-blur-xl transition lg:w-72 lg:rounded-2xl">
    <a href="/dashboard" class="mb-3 flex h-14 items-center justify-center rounded-2xl border border-sky-100/80 bg-white/82 px-3 shadow-[0_14px_30px_rgba(14,116,144,0.12)]">
        <img src="/images/logo-rotansemar.png" alt="Logo Rotan Semar" class="h-10 object-contain" />
    </a>

    <div class="space-y-3">
        @foreach ($sections as $label => $items)
            @php
                $visibleItems = array_values(array_filter($items, fn ($item) => $user && $user->can($item['permission'])));
            @endphp

            @if (count($visibleItems) > 0)
                <section class="rounded-2xl border border-white/65 bg-white/55 px-2.5 py-2 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                    <p class="mb-1 h-7 px-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-sky-700/90">
                        {{ $label }}
                    </p>

                    <nav class="space-y-1.5">
                        @foreach ($visibleItems as $item)
                            <a
                                href="{{ $item['href'] }}"
                                class="flex h-9 items-center gap-2 rounded-xl px-2.5 py-2 text-[13px] font-medium transition {{ $item['active'] ? 'bg-linear-to-r from-sky-500 to-cyan-500 text-white shadow-[0_10px_18px_rgba(14,116,144,0.34)]' : 'text-slate-600 hover:bg-sky-50/90 hover:text-slate-800' }}"
                            >
                                <span class="inline-flex h-4 w-4 items-center justify-center" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        @switch($item['icon'] ?? '')
                                            @case('dashboard')
                                                <rect x="9" y="3" width="6" height="4" rx="1" />
                                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                                                <path d="m9 14 2 2 4-4" />
                                                @break

                                            @case('pasien')
                                                <path d="M20 21a8 8 0 1 0-16 0"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                                @break

                                            @case('tindak_lanjut')
                                                <path d="M9 11h6"></path>
                                                <path d="M9 15h6"></path>
                                                <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                                                @break

                                            @case('pengambilan_obat')
                                                <path d="M12 2v20"></path>
                                                <path d="M2 12h20"></path>
                                                @break

                                            @case('laporan')
                                                <path d="M4 19h16"></path>
                                                <path d="M6 17V9"></path>
                                                <path d="M12 17V5"></path>
                                                <path d="M18 17v-7"></path>
                                                @break

                                            @case('kegiatan_penyuluhan')
                                                <path d="M3 11l18-6v14L3 13z"></path>
                                                <path d="M9 15v4"></path>
                                                @break

                                            @case('users')
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="8.5" cy="7" r="4" />
                                                <path d="M20 8v6" />
                                                <path d="M17 11h6" />
                                                @break

                                            @case('roles')
                                                <path d="M12 2 4 6v6c0 5 3.5 9.5 8 10 4.5-.5 8-5 8-10V6z"></path>
                                                @break

                                            @case('permissions')
                                                <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                                                <path d="M8 11V8a4 4 0 1 1 8 0v3"></path>
                                                @break

                                            @case('opd')
                                                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                                <path d="M7 8h10"></path>
                                                <path d="M7 12h6"></path>
                                                @break

                                            @case('fasilitas')
                                                <path d="M3 21h18"></path>
                                                <path d="M5 21V7l7-4 7 4v14"></path>
                                                <path d="M9 11h6"></path>
                                                <path d="M9 15h6"></path>
                                                @break

                                            @case('kelurahan')
                                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                                @break

                                            @case('pekerjaan')
                                                <rect x="2" y="7" width="20" height="14" rx="2"></rect>
                                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"></path>
                                                @break

                                            @case('jenis_penanganan')
                                                <path d="M9 11h6"></path>
                                                <path d="M9 15h6"></path>
                                                <path d="M9 7h6"></path>
                                                <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                                                @break

                                            @case('jenis_kebutuhan')
                                                <path d="M3 3h18v18H3z"></path>
                                                <path d="M8 3v18"></path>
                                                @break

                                            @default
                                                <circle cx="12" cy="12" r="4"></circle>
                                        @endswitch
                                    </svg>
                                </span>
                                <span>{{ $item['title'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </section>
            @endif
        @endforeach
    </div>
</aside>
