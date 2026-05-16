<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script>
            (function () {
                const appearance = '{{ $appearance ?? "system" }}';
                if (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            })();

            function resolveActionType(label) {
                const text = label.toLowerCase();

                if (/\b(tambah|buat|create|baru|import)\b/.test(text)) return 'add';
                if (/\b(edit|ubah)\b/.test(text)) return 'edit';
                if (/\b(hapus|delete|remove)\b/.test(text)) return 'delete';
                if (/\b(detail|lihat|show)\b/.test(text)) return 'detail';
                if (/\b(simpan|save|submit|update)\b/.test(text)) return 'save';
                if (/\b(kembali|back|batal|cancel)\b/.test(text)) return 'neutral';
                if (/\b(cari|filter|terapkan|apply)\b/.test(text)) return 'search';
                if (/\b(download|export|unduh)\b/.test(text)) return 'download';

                return null;
            }

            function createActionIcon(type) {
                const ns = 'http://www.w3.org/2000/svg';
                const svg = document.createElementNS(ns, 'svg');
                svg.setAttribute('class', 'ui-action-icon');
                svg.setAttribute('viewBox', '0 0 24 24');
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', 'currentColor');
                svg.setAttribute('stroke-width', '2');
                svg.setAttribute('stroke-linecap', 'round');
                svg.setAttribute('stroke-linejoin', 'round');
                svg.setAttribute('aria-hidden', 'true');

                const pathsByType = {
                    add: ['M12 5v14', 'M5 12h14'],
                    edit: ['M12 20h9', 'M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z'],
                    delete: ['M3 6h18', 'M8 6V4h8v2', 'M19 6l-1 14H6L5 6'],
                    detail: ['M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                    save: ['M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z', 'M17 21v-8H7v8', 'M7 3v5h8'],
                    neutral: ['M15 18l-6-6 6-6'],
                    search: ['m21 21-4.3-4.3', 'M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z'],
                    download: ['M12 3v12', 'm7 10 5 5 5-5', 'M5 21h14'],
                };

                const paths = pathsByType[type] || ['M12 12h.01'];
                paths.forEach(function (d) {
                    const path = document.createElementNS(ns, 'path');
                    path.setAttribute('d', d);
                    svg.appendChild(path);
                });

                return svg;
            }

            function normalizeActionButtons() {
                const candidates = document.querySelectorAll('main a, main button');

                candidates.forEach(function (el) {
                    if (el.id === 'sidebar-toggle') return;
                    if (el.closest('#admin-sidebar')) return;
                    if (el.closest('form[action$="logout"]')) return;
                    if (el.closest('[aria-label="Pagination"], .pagination')) return;
                    if (el.hasAttribute('data-no-unify')) return;

                    const label = (el.textContent || '').replace(/\s+/g, ' ').trim();
                    if (!label) return;

                    const type = resolveActionType(label);
                    if (!type) return;

                    el.classList.add('ui-action-btn', 'ui-action-btn--' + type);

                    const hasIcon = !!el.querySelector('svg, .ui-action-icon');
                    if (!hasIcon) {
                        const icon = createActionIcon(type);
                        el.prepend(icon);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('admin-sidebar');
                const toggle = document.getElementById('sidebar-toggle');

                if (sidebar && toggle) {
                    toggle.addEventListener('click', function () {
                        const isHidden = sidebar.classList.toggle('hidden');
                        toggle.setAttribute('aria-expanded', String(!isHidden));
                    });
                }

                normalizeActionButtons();
            });
        </script>

        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }

            .ui-action-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 0.5rem !important;
                height: 2.5rem !important;
                border-radius: 0.5rem !important;
                padding: 0 1rem !important;
                border-width: 1px !important;
                font-size: 0.875rem !important;
                font-weight: 500 !important;
                line-height: 1 !important;
                text-decoration: none !important;
                transition: all 0.2s ease !important;
            }

            .ui-action-icon {
                width: 1rem;
                height: 1rem;
                flex: 0 0 auto;
            }

            .ui-action-btn--add,
            .ui-action-btn--save {
                background: #2563eb !important;
                border-color: #1d4ed8 !important;
                color: #ffffff !important;
            }

            .ui-action-btn--add:hover,
            .ui-action-btn--save:hover {
                background: #1d4ed8 !important;
            }

            .ui-action-btn--edit {
                background: #fef3c7 !important;
                border-color: #f59e0b !important;
                color: #92400e !important;
            }

            .ui-action-btn--edit:hover {
                background: #fde68a !important;
            }

            .ui-action-btn--delete {
                background: #fef2f2 !important;
                border-color: #fca5a5 !important;
                color: #b91c1c !important;
            }

            .ui-action-btn--delete:hover {
                background: #fee2e2 !important;
            }

            .ui-action-btn--detail,
            .ui-action-btn--search {
                background: #ecfeff !important;
                border-color: #67e8f9 !important;
                color: #0e7490 !important;
            }

            .ui-action-btn--detail:hover,
            .ui-action-btn--search:hover {
                background: #cffafe !important;
            }

            .ui-action-btn--download {
                background: #ecfdf5 !important;
                border-color: #6ee7b7 !important;
                color: #047857 !important;
            }

            .ui-action-btn--download:hover {
                background: #d1fae5 !important;
            }

            .ui-action-btn--neutral {
                background: #ffffff !important;
                border-color: #d1d5db !important;
                color: #374151 !important;
            }

            .ui-action-btn--neutral:hover {
                background: #f9fafb !important;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/app.css'])
        <title>{{ isset($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>
    </head>
    <body class="min-h-screen font-sans antialiased app-shell-bg">
        @php
            $path = request()->path();
            $currentLabel = $path === 'dashboard'
                ? 'Dashboard'
                : ucfirst(str_replace(['-', '/'], ' ', trim($path, '/')));
            $user = auth()->user();
        @endphp

        <div class="mx-auto max-w-[1600px] px-3 py-3 md:px-4 lg:px-6">
            <div class="flex min-h-[calc(100vh-1.5rem)] flex-col gap-3 lg:flex-row">
                @include('layouts.partials.admin-sidebar')

                <main class="flex-1">
                    <header class="sticky top-0 z-20 mb-3">
                        <div class="flex min-h-16 items-center justify-between gap-3 rounded-[22px] border border-white/60 bg-white/76 px-4 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                            <button
                                id="sidebar-toggle"
                                type="button"
                                aria-controls="admin-sidebar"
                                aria-expanded="true"
                                class="inline-flex h-10 items-center gap-2 rounded-xl border border-sky-200/70 bg-white/85 px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-sky-50 hover:text-sky-700"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <line x1="3" y1="12" x2="21" y2="12"></line>
                                    <line x1="3" y1="18" x2="21" y2="18"></line>
                                </svg>
                            </button>

                            <div class="flex items-center gap-3">
                                <div class="hidden text-right sm:block">
                                    <p class="text-sm font-semibold text-slate-700">{{ $user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user?->email }}</p>
                                </div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-sky-200/70 bg-white/85 px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-sky-50 hover:text-sky-700">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </header>

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
