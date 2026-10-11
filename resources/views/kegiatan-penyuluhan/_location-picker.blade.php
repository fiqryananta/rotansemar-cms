{{-- Modal peta pemilih lokasi, disalin dari form pasien (resources/views/pasiens/_form.blade.php)
     supaya cara mengisi koordinat sama persis di seluruh aplikasi.

     Sengaja ditaruh di luar <form> supaya tombol di dalamnya tidak ikut ter-submit, dan
     tombolnya diberi type="button" sebagai pengaman ganda. Semua kelas Tailwind di sini
     sudah ada di bundle hasil build, jadi tidak perlu menjalankan `npm run build`; dua
     ukuran yang tidak punya padanan utility (lebar dialog & tinggi peta) ditulis inline.

     Dipakai oleh kegiatan-penyuluhan/create dan edit: keduanya punya input hidden
     #coordinates (name="koordinat_lokasi") dan tombol #coordinates-picker. --}}
<div
    id="location-picker"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="location-picker-title"
>
    <div class="flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-xl" style="max-width:64rem;">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
            <div class="min-w-0">
                <h2 id="location-picker-title" class="text-base font-semibold text-gray-900">Pilih Lokasi</h2>
            </div>
            <button
                type="button"
                id="location-picker-close"
                data-no-unify
                aria-label="Tutup"
                class="inline-flex h-9 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white px-3 text-gray-500 transition hover:bg-gray-50"
            >
                <i class="ti ti-x" style="font-size:1rem;" aria-hidden="true"></i>
            </button>
        </div>

        {{-- overflow-hidden + isolation: pane internal Leaflet punya z-index sampai 700,
             jadi kontainernya dijadikan stacking context sendiri supaya peta tidak bisa
             menimpa header maupun footer dialog. --}}
        <div class="relative overflow-hidden" style="isolation:isolate;">
            <div id="location-picker-map" style="height:60vh;"></div>
            <div id="location-picker-error" class="absolute inset-0 hidden items-center justify-center bg-white p-6 text-center text-sm text-gray-500">
                Gagal memuat peta. Periksa koneksi internet Anda, lalu tutup dan buka kembali dialog ini.
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 px-4 py-3 sm:flex-row sm:items-center">
            <p class="text-sm text-gray-500">
                Koordinat terpilih: <span id="location-picker-value" class="font-medium text-gray-900">-</span>
            </p>
            <div class="flex gap-3 sm:ml-auto">
                <button type="button" id="location-picker-cancel" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</button>
                <button type="button" id="location-picker-save" class="inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 text-sm font-medium text-white transition hover:bg-cyan-700">Simpan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const trigger = document.getElementById('coordinates-picker');
    const hiddenInput = document.getElementById('coordinates');
    const label = document.getElementById('coordinates-label');
    const overlay = document.getElementById('location-picker');
    const mapEl = document.getElementById('location-picker-map');
    const errorEl = document.getElementById('location-picker-error');
    const valueEl = document.getElementById('location-picker-value');
    const saveBtn = document.getElementById('location-picker-save');
    const cancelBtn = document.getElementById('location-picker-cancel');
    const closeBtn = document.getElementById('location-picker-close');

    if (!trigger || !hiddenInput || !label || !overlay || !mapEl || !saveBtn) {
        return;
    }

    // Titik tengah awal: Kota Semarang, dipakai kalau kegiatan belum punya koordinat.
    const DEFAULT_CENTER = [-7.0051, 110.4381];
    const DEFAULT_ZOOM = 12;
    const PIN_ZOOM = 17;

    // Leaflet + tile OpenStreetMap: tidak butuh API key sama sekali.
    const LEAFLET_CSS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    const LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

    let map = null;
    let marker = null;
    let leafletPromise = null;
    // Koordinat yang sedang digarap di dalam modal. Baru disalin ke input hidden saat Simpan,
    // jadi menutup modal dengan Batal tidak mengubah nilai yang tersimpan.
    let draft = null;

    // Menerima "lat, lng" (pemisah koma atau titik koma, spasi bebas) dan menolak
    // apa pun yang di luar rentang lintang/bujur yang sah.
    const parseCoordinates = (value) => {
        const match = /(-?\d+(?:[.,]\d+)?)\s*[,;]\s*(-?\d+(?:[.,]\d+)?)/.exec(String(value == null ? '' : value));

        if (!match) {
            return null;
        }

        const lat = parseFloat(match[1].replace(',', '.'));
        const lng = parseFloat(match[2].replace(',', '.'));

        if (!isFinite(lat) || !isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
            return null;
        }

        return { lat: lat, lng: lng };
    };

    const formatCoordinates = (lat, lng) => lat.toFixed(6) + ', ' + lng.toFixed(6);

    const paintDraft = () => {
        valueEl.textContent = draft ? formatCoordinates(draft.lat, draft.lng) : '-';

        // Belum ada titik yang dipilih: Simpan dimatikan supaya tidak ada koordinat
        // asal-asalan yang tersimpan hanya karena tombolnya tertekan.
        saveBtn.disabled = !draft;
        saveBtn.style.opacity = draft ? '' : '0.5';
        saveBtn.style.cursor = draft ? '' : 'not-allowed';
    };

    // Pin dibuat saat titik pertama dipilih, bukan sebelumnya: kalau tidak,
    // pin akan nangkring di titik tengah default padahal belum ada koordinat.
    const placeMarker = (lat, lng) => {
        if (marker) {
            marker.setLatLng([lat, lng]);
            return;
        }

        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', () => {
            const position = marker.getLatLng();
            setDraft(position.lat, position.lng);
        });
    };

    const clearMarker = () => {
        if (!marker) {
            return;
        }

        map.removeLayer(marker);
        marker = null;
    };

    const setDraft = (lat, lng) => {
        draft = { lat: lat, lng: lng };
        paintDraft();
        placeMarker(lat, lng);
    };

    // Leaflet-nya baru diunduh saat modal pertama kali dibuka, supaya halaman form
    // kegiatan tidak menanggung beratnya kalau petanya memang tidak dipakai.
    const loadLeaflet = () => {
        if (leafletPromise) {
            return leafletPromise;
        }

        leafletPromise = new Promise((resolve, reject) => {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = LEAFLET_CSS;
            document.head.appendChild(css);

            const script = document.createElement('script');
            script.src = LEAFLET_JS;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Leaflet gagal dimuat'));
            document.head.appendChild(script);
        });

        // Jangan simpan promise yang sudah gagal selamanya, biar percobaan
        // berikutnya masih bisa mengunduh ulang.
        leafletPromise.catch(() => {
            leafletPromise = null;
        });

        return leafletPromise;
    };

    const buildMap = () => {
        map = L.map(mapEl, {
            center: draft ? [draft.lat, draft.lng] : DEFAULT_CENTER,
            zoom: draft ? PIN_ZOOM : DEFAULT_ZOOM,
        });

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        map.on('click', (event) => setDraft(event.latlng.lat, event.latlng.lng));

        if (draft) {
            placeMarker(draft.lat, draft.lng);
        }
    };

    const open = () => {
        draft = parseCoordinates(hiddenInput.value);
        paintDraft();

        mapEl.classList.remove('hidden');
        errorEl.classList.add('hidden');
        errorEl.classList.remove('flex');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        loadLeaflet()
            .then(() => {
                if (!map) {
                    buildMap();
                } else {
                    // Dibuka ulang: kembalikan pin ke koordinat yang tersimpan,
                    // bukan ke posisi hasil editan yang tadi dibatalkan.
                    const center = draft ? [draft.lat, draft.lng] : DEFAULT_CENTER;
                    map.setView(center, draft ? PIN_ZOOM : DEFAULT_ZOOM);

                    if (draft) {
                        placeMarker(draft.lat, draft.lng);
                    } else {
                        clearMarker();
                    }
                }

                // Kontainer baru saja berubah dari hidden jadi terlihat, jadi Leaflet
                // perlu diberi tahu ukuran barunya sebelum petanya digambar.
                window.requestAnimationFrame(() => map.invalidateSize());

                // Tombol yang disabled tidak bisa menerima fokus, jadi jatuhkan ke Batal.
                (saveBtn.disabled ? cancelBtn : saveBtn).focus();
            })
            .catch(() => {
                mapEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
                errorEl.classList.add('flex');
                cancelBtn.focus();
            });
    };

    const close = () => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        trigger.focus();
    };

    const commit = () => {
        if (!draft) {
            return;
        }

        const value = formatCoordinates(draft.lat, draft.lng);
        hiddenInput.value = value;
        label.textContent = value;
        close();
    };

    trigger.addEventListener('click', open);
    saveBtn.addEventListener('click', commit);
    cancelBtn.addEventListener('click', close);
    closeBtn.addEventListener('click', close);

    // Klik di area gelap (bukan di dalam dialog) berfungsi seperti Batal.
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
            close();
        }
    });
})();
</script>
@endpush
