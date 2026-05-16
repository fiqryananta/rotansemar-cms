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

                if (/\b(tambah|buat|create|baru)\b/.test(text)) return 'add';
                if (/\b(import|impor)\b/.test(text)) return 'import';
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
                const iconMap = {
                    add:      'ti-plus',
                    edit:     'ti-pencil',
                    delete:   'ti-trash',
                    detail:   'ti-eye',
                    save:     'ti-device-floppy',
                    neutral:  'ti-chevron-left',
                    search:   'ti-search',
                    download: 'ti-download',
                };
                const icon = document.createElement('i');
                icon.className = 'ti ' + (iconMap[type] || 'ti-circle') + ' ui-action-icon';
                icon.setAttribute('aria-hidden', 'true');
                return icon;
            }

            function normalizeActionButtons() {
                const candidates = document.querySelectorAll('main a, main button');

                candidates.forEach(function (el) {
                    if (el.id === 'sidebar-toggle') return;
                    if (el.closest('#admin-sidebar')) return;
                    if (el.closest('#profile-dropdown-wrap')) return;
                    if (el.closest('form[action$="logout"]')) return;
                    if (el.closest('[aria-label="Pagination"], .pagination')) return;
                    if (el.hasAttribute('data-no-unify')) return;

                    const label = (el.textContent || '').replace(/\s+/g, ' ').trim();
                    if (!label) return;

                    const type = resolveActionType(label);
                    if (!type) return;

                    el.classList.add('ui-action-btn', 'ui-action-btn--' + type);

                    const hasIcon = !!el.querySelector('svg, i.ti, .ui-action-icon');
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
                font-size: 1rem;
                flex: 0 0 auto;
                line-height: 1;
                vertical-align: -0.125em;
            }

            .ui-action-btn--import {
                background: #25bdeb !important;
                border-color: #1792b8 !important;
                color: #ffffff !important;
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

            /* ── Custom Confirm Dialog ────────────────────────────── */
            #confirm-overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(15, 23, 42, 0.45);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.2s ease, visibility 0.2s ease;
            }

            #confirm-overlay.is-open {
                opacity: 1;
                visibility: visible;
            }

            #confirm-dialog {
                background: #ffffff;
                border-radius: 1.25rem;
                box-shadow: 0 25px 60px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(226, 232, 240, 0.8);
                width: 100%;
                max-width: 26rem;
                padding: 2rem;
                transform: scale(0.92) translateY(8px);
                transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.25rem;
                text-align: center;
            }

            #confirm-overlay.is-open #confirm-dialog {
                transform: scale(1) translateY(0);
            }

            .confirm-icon-wrap {
                width: 3.5rem;
                height: 3.5rem;
                border-radius: 50%;
                background: #fff1f2;
                border: 2px solid #fecdd3;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 0.75rem;
            }

            .confirm-icon-wrap i.ti {
                font-size: 1.75rem;
                color: #e11d48;
            }

            #confirm-title {
                font-size: 1.0625rem;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 0.375rem;
            }

            #confirm-message {
                font-size: 0.875rem;
                color: #64748b;
                line-height: 1.6;
                margin: 0 0 1.5rem;
            }

            .confirm-actions {
                display: flex;
                gap: 0.75rem;
                width: 100%;
            }

            #confirm-cancel {
                flex: 1;
                height: 2.625rem;
                border-radius: 0.75rem;
                border: 1.5px solid #e2e8f0;
                background: #f8fafc;
                color: #475569;
                font-size: 0.875rem;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.15s, border-color 0.15s;
            }

            #confirm-cancel:hover {
                background: #f1f5f9;
                border-color: #cbd5e1;
            }

            #confirm-ok {
                flex: 1;
                height: 2.625rem;
                border-radius: 0.75rem;
                border: none;
                background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
                color: #ffffff;
                font-size: 0.875rem;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
                transition: filter 0.15s, box-shadow 0.15s;
            }

            #confirm-ok:hover {
                filter: brightness(1.07);
                box-shadow: 0 6px 16px rgba(225, 29, 72, 0.4);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">

        @fonts
        @vite(['resources/css/app.css'])
        <title>{{ isset($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

        {{-- Plus Jakarta Sans --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

        {{-- Override font to Plus Jakarta Sans (overrides Vite-built --font-sans) --}}
        <style>
            :root {
                --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif,
                    'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            }
            html, body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        </style>

        {{-- Tabler Icons --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
        <style>
            i.ti {
                display: inline-block;
                line-height: 1;
                vertical-align: -0.125em;
                flex-shrink: 0;
                font-style: normal;
            }
        </style>

        {{-- Flatpickr date picker --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <style>
            .flatpickr-input[readonly] { cursor: pointer; }
            .flatpickr-alt-input { background-color: white !important; }
        </style>

        {{-- Select2 --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <style>
            .select2-container { display: block; }
            .select2-container--default .select2-selection--single {
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                height: 2.5rem;
                display: flex;
                align-items: center;
                background-color: #fff;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #111827;
                line-height: normal;
                padding-left: 0.75rem;
                padding-right: 2rem;
                font-size: 0.875rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9ca3af; }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 2.5rem; right: 0.5rem; }
            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #6366f1;
                outline: none;
                box-shadow: 0 0 0 1px #6366f1;
            }
            .select2-container--default.select2-container--disabled .select2-selection--single {
                background-color: #f9fafb;
                cursor: not-allowed;
            }
            .select2-dropdown {
                border-color: #d1d5db;
                border-radius: 0.375rem;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.06);
            }
            .select2-search--dropdown .select2-search__field {
                border: 1px solid #d1d5db;
                border-radius: 0.25rem;
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
                outline: none;
            }
            .select2-search--dropdown .select2-search__field:focus { border-color: #6366f1; }
            .select2-results__option { font-size: 0.875rem; padding: 0.375rem 0.75rem; }
            .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #6366f1; }
            .select2-container--default .select2-results__option[aria-selected="true"] { background-color: #eef2ff; color: #4f46e5; }
        </style>
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
                                <i class="ti ti-menu-2" style="font-size:1rem;" aria-hidden="true"></i>
                            </button>

                            {{-- Profile dropdown --}}
                            <div class="relative" id="profile-dropdown-wrap">
                                <button
                                    type="button"
                                    id="profile-dropdown-btn"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    data-no-unify
                                    class="flex items-center gap-3 rounded-xl border border-sky-200/70 bg-white/85 px-3 py-2 shadow-sm transition hover:bg-sky-50"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-600 text-sm font-bold text-white select-none">
                                        {{ mb_strtoupper(mb_substr($user?->name ?? 'U', 0, 1)) }}
                                    </span>
                                    <span class="hidden sm:block h-6 w-px bg-sky-200/70"></span>
                                    <div class="hidden text-left sm:block max-w-[120px]">
                                        <p class="truncate text-sm font-semibold leading-tight text-slate-700">{{ $user?->name }}</p>
                                        <p class="truncate text-xs leading-tight text-slate-400">{{ $user?->email }}</p>
                                    </div>
                                    <i class="ti ti-chevron-down leading-none text-slate-400 transition-transform duration-200" id="profile-chevron" style="font-size:0.85rem;" aria-hidden="true"></i>
                                </button>

                                {{-- Dropdown menu --}}
                                <div
                                    id="profile-dropdown-menu"
                                    role="menu"
                                    aria-labelledby="profile-dropdown-btn"
                                    class="absolute right-0 top-full z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl"
                                    style="display:none;"
                                >

                                    {{-- Menu items --}}
                                    <div class="py-2">
                                        <a
                                            href="{{ route('profile.change-password') }}"
                                            role="menuitem"
                                            data-no-unify
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 transition-colors hover:bg-sky-50 hover:text-sky-700"
                                        >
                                            <i class="ti ti-lock" style="font-size:1rem;" aria-hidden="true"></i>
                                            Ubah Password
                                        </a>
                                    </div>

                                    <div class="border-t border-gray-100 py-2">
                                        <form
                                            method="POST"
                                            action="{{ route('logout') }}"
                                            data-confirm="Apakah Anda yakin ingin keluar dari aplikasi?"
                                            data-confirm-title="Konfirmasi Logout"
                                            data-confirm-icon="ti-logout"
                                            data-confirm-ok="Ya, Logout"
                                            data-confirm-color="blue"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                role="menuitem"
                                                data-no-unify
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-rose-600 transition-colors hover:bg-rose-50"
                                            >
                                                <i class="ti ti-logout" style="font-size:1rem;" aria-hidden="true"></i>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    @yield('content')
                </main>
            </div>
        </div>
        {{-- Custom Confirm Dialog --}}
        <div id="confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message">
            <div id="confirm-dialog">
                <div class="confirm-icon-wrap" id="confirm-icon-wrap">
                    <i class="ti ti-trash" id="confirm-icon" aria-hidden="true"></i>
                </div>
                <p id="confirm-title">Konfirmasi Hapus</p>
                <p id="confirm-message"></p>
                <div class="confirm-actions">
                    <button id="confirm-cancel" type="button">Batal</button>
                    <button id="confirm-ok" type="button">Ya, Hapus</button>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var overlay = document.getElementById('confirm-overlay');
            var msgEl   = document.getElementById('confirm-message');
            var okBtn   = document.getElementById('confirm-ok');
            var cancelBtn = document.getElementById('confirm-cancel');
            var pendingForm = null;

            var titleEl   = document.getElementById('confirm-title');
            var iconWrap  = document.getElementById('confirm-icon-wrap');
            var iconEl    = document.getElementById('confirm-icon');

            var DEFAULTS = {
                title: 'Konfirmasi Hapus',
                icon:  'ti-trash',
                ok:    'Ya, Hapus',
                color: 'red',
            };

            function applyConfirmTheme(form) {
                var title = (form && form.getAttribute('data-confirm-title')) || DEFAULTS.title;
                var icon  = (form && form.getAttribute('data-confirm-icon'))  || DEFAULTS.icon;
                var ok    = (form && form.getAttribute('data-confirm-ok'))    || DEFAULTS.ok;
                var color = (form && form.getAttribute('data-confirm-color')) || DEFAULTS.color;

                titleEl.textContent  = title;
                iconEl.className     = 'ti ' + icon;
                okBtn.textContent    = ok;

                if (color === 'blue') {
                    iconWrap.style.background   = '#eff6ff';
                    iconWrap.style.borderColor  = '#bfdbfe';
                    iconEl.style.color          = '#2563eb';
                    okBtn.style.background      = 'linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)';
                    okBtn.style.boxShadow       = '0 4px 12px rgba(37,99,235,0.3)';
                } else {
                    iconWrap.style.background   = '';
                    iconWrap.style.borderColor  = '';
                    iconEl.style.color          = '';
                    okBtn.style.background      = '';
                    okBtn.style.boxShadow       = '';
                }
            }

            function openConfirm(message, form) {
                pendingForm = form;
                msgEl.textContent = message;
                applyConfirmTheme(form);
                overlay.classList.add('is-open');
                okBtn.focus();
            }

            function closeConfirm() {
                overlay.classList.remove('is-open');
                pendingForm = null;
            }

            okBtn.addEventListener('click', function () {
                if (pendingForm) {
                    var form = pendingForm;
                    closeConfirm();
                    form.submit();
                }
            });

            cancelBtn.addEventListener('click', closeConfirm);

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeConfirm();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
                    closeConfirm();
                }
            });

            document.addEventListener('submit', function (e) {
                var form = e.target;
                var submitter = e.submitter;
                var message = form.getAttribute('data-confirm')
                           || (submitter && submitter.getAttribute('data-confirm'))
                           || null;
                if (!message) return;
                e.preventDefault();
                openConfirm(message, form);
            }, true);
        })();
        </script>

        {{-- jQuery + Select2 --}}
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
        (function () {
            var S2_BASE = {
                minimumResultsForSearch: 7,
                language: {
                    noResults: function () { return 'Tidak ada hasil.'; },
                    searching: function () { return 'Mencari\u2026'; },
                },
            };

            function initSelect2(root) {
                $(root || document).find('select:not([data-s2-init])').each(function () {
                    $(this).attr('data-s2-init', '1').select2(Object.assign({}, S2_BASE, {
                        width: this.classList.contains('w-full') ? '100%' : 'resolve',
                    }));
                });
            }

            // Re-dispatch native 'change' so vanilla addEventListener handlers still work
            $(document).on('select2:select select2:unselect select2:clear', 'select', function () {
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });

            $(document).ready(function () { initSelect2(); });

            // Intercept select.value = x so Select2 UI stays in sync
            (function () {
                var desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
                if (!desc) return;
                Object.defineProperty(HTMLSelectElement.prototype, 'value', {
                    set: function (v) {
                        desc.set.call(this, v);
                        if (this.dataset.s2Init && window.$) {
                            try { $(this).trigger('change.select2'); } catch (e) {}
                        }
                    },
                    get: desc.get,
                    configurable: true,
                });
            })();

            // MutationObserver: init new selects + refresh when options change
            new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (n) {
                        if (n.nodeType !== 1) return;
                        if (n.tagName === 'SELECT') initSelect2(n.parentElement);
                        else if (n.querySelectorAll && n.querySelectorAll('select:not([data-s2-init])').length) initSelect2(n);
                    });
                    if (m.target && m.target.tagName === 'SELECT' && m.target.dataset.s2Init) {
                        try { $(m.target).trigger('change.select2'); } catch (e) {}
                    }
                });
            }).observe(document.body, { childList: true, subtree: true });

            // MutationObserver: refresh Select2 when disabled attr changes
            new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    if (m.target.tagName === 'SELECT' && m.target.dataset.s2Init) {
                        try { $(m.target).trigger('change.select2'); } catch (e) {}
                    }
                });
            }).observe(document.body, { attributes: true, attributeFilter: ['disabled'], subtree: true });

            window._initSelect2 = initSelect2;
        })();
        </script>

        {{-- Flatpickr JS --}}
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
        <script>
        (function () {
            flatpickr.localize(flatpickr.l10ns.id);

            function initFp(el) {
                if (el._flatpickr || el.dataset.fpInit) return;
                el.dataset.fpInit = '1';
                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'j F Y',
                    allowInput: true,
                });
            }

            function initAll(root) {
                (root || document).querySelectorAll('input[type="date"]:not([data-fp-init])').forEach(initFp);
            }

            document.addEventListener('DOMContentLoaded', function () {
                initAll();
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        m.addedNodes.forEach(function (n) {
                            if (n.nodeType !== 1) return;
                            if (n.matches && n.matches('input[type="date"]')) initFp(n);
                            if (n.querySelectorAll) initAll(n);
                        });
                    });
                }).observe(document.body, { childList: true, subtree: true });
            });

            window._initFlatpickr = initAll;
        })();
        </script>

        {{-- Profile dropdown JS --}}
        <script>
        (function () {
            var btn    = document.getElementById('profile-dropdown-btn');
            var menu   = document.getElementById('profile-dropdown-menu');
            var chevron = document.getElementById('profile-chevron');
            if (!btn || !menu) return;

            function openMenu() {
                menu.style.display = 'block';
                btn.setAttribute('aria-expanded', 'true');
                chevron.style.transform = 'rotate(180deg)';
            }

            function closeMenu() {
                menu.style.display = 'none';
                btn.setAttribute('aria-expanded', 'false');
                chevron.style.transform = '';
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.style.display === 'none' ? openMenu() : closeMenu();
            });

            document.addEventListener('click', function (e) {
                if (!document.getElementById('profile-dropdown-wrap').contains(e.target)) {
                    closeMenu();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });
        })();
        </script>

        @stack('scripts')
    </body>
</html>
