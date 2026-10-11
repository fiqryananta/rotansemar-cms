{{-- Modal peta penampil lokasi (hanya baca). Dipakai halaman detail yang menampilkan
     koordinat: pasien dan kegiatan penyuluhan.

     Gayanya mengikuti modal pilih lokasi
     (resources/views/kegiatan-penyuluhan/_location-picker.blade.php), tapi di sini
     tidak ada Simpan/Batal dan pin-nya tidak bisa digeser -- modal ini hanya
     memperlihatkan titik yang sudah tersimpan.

     Pemicunya adalah tombol mana pun yang punya atribut data-location-view berisi
     string koordinat "lat, lng". Jadi satu halaman boleh punya beberapa tombol
     sekaligus, dan partial ini tidak butuh variabel apa pun dari view pemanggil.

     Semua kelas Tailwind di sini sudah ada di bundle hasil build, jadi tidak perlu
     menjalankan `npm run build`; lebar dialog dan tinggi peta ditulis inline
     karena tidak punya padanan utility. Warna ikon pemicunya sendiri diatur oleh
     kelas .location-view-trigger di layout. --}}
<div
    id="location-viewer"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="location-viewer-title"
>
    <div class="flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-xl" style="max-width:64rem;">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
            <div class="min-w-0">
                <h2 id="location-viewer-title" class="text-base font-semibold text-gray-900">Lokasi</h2>
            </div>
            <button
                type="button"
                id="location-viewer-close"
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
            <div id="location-viewer-map" style="height:60vh;"></div>
            {{-- Satu kotak pesan untuk dua keadaan: koordinatnya tidak terbaca, atau
                 Leaflet-nya gagal diunduh. Teksnya diisi dari JS. --}}
            <div id="location-viewer-message" class="absolute inset-0 hidden items-center justify-center bg-white p-6 text-center text-sm text-gray-500"></div>
        </div>

        <div class="flex items-center gap-3 border-t border-gray-200 px-4 py-3">
            <p class="min-w-0 flex-1 truncate text-sm text-gray-500">
                Koordinat: <span id="location-viewer-value" class="font-medium text-gray-900">-</span>
            </p>
            <button type="button" id="location-viewer-done" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const overlay = document.getElementById('location-viewer');
    const mapEl = document.getElementById('location-viewer-map');
    const messageEl = document.getElementById('location-viewer-message');
    const valueEl = document.getElementById('location-viewer-value');
    const closeBtn = document.getElementById('location-viewer-close');
    const doneBtn = document.getElementById('location-viewer-done');
    const triggers = document.querySelectorAll('[data-location-view]');

    if (!overlay || !mapEl || !messageEl || !valueEl || !closeBtn || !doneBtn || !triggers.length) {
        return;
    }

    // Titik yang tersimpan selalu digambar sedekat ini, karena yang dilihat cuma satu lokasi.
    const PIN_ZOOM = 17;

    // Leaflet + tile OpenStreetMap: tidak butuh API key sama sekali.
    const LEAFLET_CSS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    const LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

    let map = null;
    let marker = null;
    let leafletPromise = null;
    // Koordinat yang sedang ditampilkan, dan tombol yang membukanya (untuk
    // mengembalikan fokus saat modal ditutup).
    let target = null;
    let lastTrigger = null;

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

    // Leaflet-nya baru diunduh saat modal pertama kali dibuka, supaya halaman detail
    // tidak menanggung beratnya kalau petanya memang tidak pernah dibuka.
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

    const showMessage = (text) => {
        mapEl.classList.add('hidden');
        messageEl.textContent = text;
        messageEl.classList.remove('hidden');
        messageEl.classList.add('flex');
    };

    const hideMessage = () => {
        mapEl.classList.remove('hidden');
        messageEl.classList.add('hidden');
        messageEl.classList.remove('flex');
    };

    const buildMap = () => {
        map = L.map(mapEl, {
            center: [target.lat, target.lng],
            zoom: PIN_ZOOM,
        });

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
    };

    // Pin sengaja tanpa draggable: modal ini bukan sarana mengubah koordinat.
    const placeMarker = () => {
        if (marker) {
            marker.setLatLng([target.lat, target.lng]);
            return;
        }

        marker = L.marker([target.lat, target.lng]).addTo(map);
    };

    const open = (trigger) => {
        lastTrigger = trigger;
        target = parseCoordinates(trigger.dataset.locationView);

        // Yang ditampilkan di footer adalah nilai apa adanya seperti di halaman,
        // bukan hasil normalisasi JS, supaya keduanya tidak pernah beda bacaan.
        valueEl.textContent = trigger.dataset.locationView || '-';

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        if (!target) {
            showMessage('Koordinat "' + valueEl.textContent + '" tidak bisa dibaca, jadi titiknya tidak bisa digambar di peta.');
            closeBtn.focus();
            return;
        }

        hideMessage();

        loadLeaflet()
            .then(() => {
                if (!map) {
                    buildMap();
                } else {
                    // Modal dibuka dari tombol lain: pindahkan peta ke titik yang baru.
                    map.setView([target.lat, target.lng], PIN_ZOOM);
                }

                placeMarker();

                // Kontainer baru saja berubah dari hidden jadi terlihat, jadi Leaflet
                // perlu diberi tahu ukuran barunya sebelum petanya digambar.
                window.requestAnimationFrame(() => map.invalidateSize());

                doneBtn.focus();
            })
            .catch(() => {
                showMessage('Gagal memuat peta. Periksa koneksi internet Anda, lalu tutup dan buka kembali dialog ini.');
                closeBtn.focus();
            });
    };

    const close = () => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');

        if (lastTrigger) {
            lastTrigger.focus();
        }
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => open(trigger));
    });

    closeBtn.addEventListener('click', close);
    doneBtn.addEventListener('click', close);

    // Klik di area gelap (bukan di dalam dialog) ikut menutup.
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
