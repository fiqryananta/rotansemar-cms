{{--
    Pemilih kelurahan cakupan, dikelompokkan per kecamatan.
    Tiap kecamatan jadi panel yang bisa dibuka/tutup (accordion) dan punya
    checkbox "Pilih semua" untuk mencentang seluruh kelurahan di kecamatan itu.

    Dipakai bersama oleh puskesmas/create.blade.php dan puskesmas/edit.blade.php.

    Variabel yang dibutuhkan:
    - $kecamatans         : collection Kecamatan dengan relasi kelurahans
    - $selectedKelurahans : array id kelurahan yang sedang terpilih
--}}

@php
    $selectedKelurahans = $selectedKelurahans ?? [];
@endphp

<div>
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="block text-sm font-medium text-gray-700">Kelurahan Cakupan</p>
        <span class="text-xs text-gray-500">
            <span data-kelurahan-total class="font-semibold text-emerald-700">{{ count($selectedKelurahans) }}</span> dipilih
        </span>
    </div>

    @if ($kecamatans->isEmpty())
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
            Belum ada data kelurahan. Tambahkan lewat menu Kelurahan terlebih dahulu.
        </p>
    @else
        <div class="max-h-72 space-y-2 overflow-y-auto pr-3">
            @foreach ($kecamatans as $kecamatan)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white" data-kecamatan-group>
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                        {{-- Tombol buka/tutup panel. Checkbox "Pilih semua" sengaja
                             dibuat sebagai sibling, bukan anak tombol, supaya klik
                             checkbox tidak ikut membuka/menutup panel. --}}
                        <button
                            type="button"
                            data-kecamatan-trigger
                            aria-expanded="false"
                            aria-controls="kecamatan-panel-{{ $kecamatan->id }}"
                            class="flex flex-1 items-center gap-3 rounded-md py-1 text-left"
                        >
                            <i class="ti ti-chevron-down leading-none text-gray-400 transition-transform duration-200" data-kecamatan-chevron style="font-size:0.85rem;" aria-hidden="true"></i>
                            <span>
                                <span class="block text-sm font-medium text-gray-900">{{ $kecamatan->name }}</span>
                                <span class="block text-xs text-gray-500">
                                    {{ $kecamatan->kelurahans->count() }} kelurahan &middot;
                                    <span data-kecamatan-selected>0</span> dipilih
                                </span>
                            </span>
                        </button>

                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-2 text-xs font-medium text-gray-600">
                            <input
                                type="checkbox"
                                data-kecamatan-toggle
                                aria-label="Pilih semua kelurahan di kecamatan {{ $kecamatan->name }}"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            Pilih semua
                        </label>
                    </div>

                    <div
                        id="kecamatan-panel-{{ $kecamatan->id }}"
                        class="hidden border-t border-gray-100 bg-white p-2"
                        data-kecamatan-panel
                    >
                        @foreach ($kecamatan->kelurahans as $kelurahan)
                            <label for="kelurahan-{{ $kelurahan->id }}" class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 transition hover:bg-gray-50">
                                <input
                                    id="kelurahan-{{ $kelurahan->id }}"
                                    type="checkbox"
                                    name="kelurahan_ids[]"
                                    value="{{ $kelurahan->id }}"
                                    data-kelurahan-box
                                    @checked(in_array($kelurahan->id, $selectedKelurahans))
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                <span class="text-sm text-gray-800">{{ $kelurahan->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @error('kelurahan_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('kelurahan_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

@push('scripts')
<script>
(function () {
    const groups = Array.from(document.querySelectorAll('[data-kecamatan-group]'));
    if (!groups.length) return;

    const totalLabel = document.querySelector('[data-kelurahan-total]');

    const syncTotal = () => {
        if (!totalLabel) return;
        totalLabel.textContent = String(document.querySelectorAll('[data-kelurahan-box]:checked').length);
    };

    groups.forEach((group) => {
        const boxes = Array.from(group.querySelectorAll('[data-kelurahan-box]'));
        const toggle = group.querySelector('[data-kecamatan-toggle]');
        const selectedLabel = group.querySelector('[data-kecamatan-selected]');
        const trigger = group.querySelector('[data-kecamatan-trigger]');
        const panel = group.querySelector('[data-kecamatan-panel]');
        const chevron = group.querySelector('[data-kecamatan-chevron]');

        // Perbarui hitungan kecamatan dan status checkbox "Pilih semua".
        // Sebagian tercentang -> indeterminate, agar statusnya tetap terbaca
        // walaupun panelnya sedang tertutup.
        const syncGroup = () => {
            const checked = boxes.filter((box) => box.checked).length;

            if (selectedLabel) {
                selectedLabel.textContent = String(checked);
            }

            if (toggle) {
                toggle.checked = boxes.length > 0 && checked === boxes.length;
                toggle.indeterminate = checked > 0 && checked < boxes.length;
            }
        };

        if (toggle) {
            toggle.addEventListener('change', () => {
                boxes.forEach((box) => { box.checked = toggle.checked; });
                syncGroup();
                syncTotal();
            });
        }

        boxes.forEach((box) => {
            box.addEventListener('change', () => {
                syncGroup();
                syncTotal();
            });
        });

        if (trigger && panel) {
            trigger.addEventListener('click', () => {
                const expanded = trigger.getAttribute('aria-expanded') === 'true';

                trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                panel.classList.toggle('hidden', expanded);

                if (chevron) {
                    chevron.style.transform = expanded ? '' : 'rotate(180deg)';
                }
            });
        }

        // Sinkronkan tampilan awal dengan centang yang sudah dirender server
        // (mis. hasil old() setelah validasi gagal atau cakupan yang tersimpan).
        syncGroup();
    });

    syncTotal();
})();
</script>
@endpush
