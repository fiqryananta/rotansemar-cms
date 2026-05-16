@php
    $user = auth()->user();

    $sections = [
        'Navigation' => [
            ['title' => 'Dashboard', 'href' => '/dashboard', 'permission' => 'dashboard.view', 'active' => request()->is('dashboard'), 'icon' => 'layout-dashboard'],
            ['title' => 'Pasien', 'href' => '/pasiens', 'permission' => 'pasiens.view', 'active' => request()->is('pasiens*'), 'icon' => 'users'],
            ['title' => 'Tindak Lanjut', 'href' => '/tindak-lanjut', 'permission' => 'tindak-lanjut.view', 'active' => request()->is('tindak-lanjut*'), 'icon' => 'clipboard-text'],
            ['title' => 'Pengambilan Obat', 'href' => '/medication-pickups', 'permission' => 'medication-pickups.view', 'active' => request()->is('medication-pickups*'), 'icon' => 'pill'],
        ],
        'Laporan' => [
            ['title' => 'Laporan', 'href' => '/laporan', 'permission' => 'laporan.view', 'active' => request()->is('laporan*'), 'icon' => 'chart-bar'],
        ],
        'Kegiatan' => [
            ['title' => 'Kegiatan Penyuluhan', 'href' => '/kegiatan-penyuluhan', 'permission' => 'kegiatan-penyuluhan.view', 'active' => request()->is('kegiatan-penyuluhan*'), 'icon' => 'presentation'],
        ],
        'Manajemen' => [
            ['title' => 'Users', 'href' => '/users', 'permission' => 'users.view', 'active' => request()->is('users*'), 'icon' => 'user-plus'],
            ['title' => 'Role', 'href' => '/roles', 'permission' => 'roles.view', 'active' => request()->is('roles*'), 'icon' => 'shield'],
            ['title' => 'Permission', 'href' => '/permissions', 'permission' => 'permissions.view', 'active' => request()->is('permissions*'), 'icon' => 'lock'],
        ],
        'Data Master' => [
            ['title' => 'OPD', 'href' => '/opds', 'permission' => 'opds.view', 'active' => request()->is('opds*'), 'icon' => 'building'],
            ['title' => 'Puskesmas', 'href' => '/puskesmas', 'permission' => 'puskesmas.view', 'active' => request()->is('puskesmas*'), 'icon' => 'building-hospital'],
            ['title' => 'Faskes', 'href' => '/faskes', 'permission' => 'faskes.view', 'active' => request()->is('faskes*'), 'icon' => 'first-aid-kit'],
            ['title' => 'Kecamatan', 'href' => '/kecamatans', 'permission' => 'kecamatans.view', 'active' => request()->is('kecamatans*'), 'icon' => 'map-2'],
            ['title' => 'Kelurahan', 'href' => '/kelurahans', 'permission' => 'kelurahans.view', 'active' => request()->is('kelurahans*'), 'icon' => 'map-pin'],
            ['title' => 'Pekerjaan', 'href' => '/pekerjaan', 'permission' => 'pekerjaan.view', 'active' => request()->is('pekerjaan*'), 'icon' => 'briefcase'],
            ['title' => 'Jenis Penanganan', 'href' => '/jenis-penanganans', 'permission' => 'jenis-penanganans.view', 'active' => request()->is('jenis-penanganans*'), 'icon' => 'notes'],
            ['title' => 'Jenis Kebutuhan', 'href' => '/jenis-kebutuhans', 'permission' => 'jenis-kebutuhans.view', 'active' => request()->is('jenis-kebutuhans*'), 'icon' => 'table'],
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
                                    <i class="ti ti-{{ $item['icon'] }}" style="font-size:1rem;"></i>
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
