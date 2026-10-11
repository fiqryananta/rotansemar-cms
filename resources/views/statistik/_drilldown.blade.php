{{--
    JS bersama untuk tabel rekap yang barisnya bisa dibuka jadi rincian kelurahan.
    Dipakai Rekap Wilayah dan Rekap Jenis Kebutuhan supaya perilakunya (buka/tutup,
    cache hasil, status memuat, dan penanganan gagal) tidak menyimpang satu sama lain.

    Parameter:
    - $tableId    : id elemen <table>
    - $endpoint   : URL rincian, dengan '__ID__' sebagai placeholder id kecamatan
    - $columnKeys : jalur nilai tiap kolom pada objek baris JSON, urut sesuai kolom
                    tabel. Jalur bertitik didukung, mis. 'counts.12'.
    - $emptyText  : teks saat kecamatan tidak punya kelurahan berdata
--}}
@push('scripts')
<script>
(function () {
    var table = document.getElementById(@json($tableId));
    if (!table) return;

    var tbody = table.tBodies[0];
    if (!tbody) return;

    var ENDPOINT = @json($endpoint);
    var KEYS = @json(array_values($columnKeys));
    var COLSPAN = KEYS.length;
    var EMPTY_TEXT = @json($emptyText);

    /*
     * Baris kelurahan dibangun di sisi klien, jadi kelasnya harus sama persis
     * dengan baris di tabelnya supaya tidak muncul gaya yang berbeda. Sel pertama
     * sengaja hanya pl-10 (tanpa px-4) supaya paddingnya tidak bertabrakan.
     */
    var CHILD_CLASSES = KEYS.map(function (key, index) {
        return index === 0 ? 'py-2 pl-10 text-left' : 'px-4 py-2 text-center';
    });

    // Pengambil nilai lewat jalur bertitik, supaya bentuk payload tidak perlu
    // diketahui di sini -- cukup daftar kuncinya.
    function valueAt(item, path) {
        return path.split('.').reduce(function (value, part) {
            return value == null ? value : value[part];
        }, item);
    }

    function childRow(item) {
        var tr = document.createElement('tr');
        tr.className = 'bg-sky-50 text-gray-600';
        // Disembunyikan dulu supaya tidak berkedip sebelum setExpanded jalan.
        tr.hidden = true;

        KEYS.forEach(function (key, index) {
            var value = valueAt(item, key);

            var td = document.createElement('td');
            td.className = CHILD_CLASSES[index];
            td.textContent = value == null ? '' : value;
            tr.appendChild(td);
        });

        return tr;
    }

    function messageRow(text) {
        var tr = document.createElement('tr');
        tr.className = 'bg-sky-50 text-gray-600';

        var td = document.createElement('td');
        td.className = 'px-4 py-3 text-center text-gray-400 italic';
        td.colSpan = COLSPAN;
        td.textContent = text;

        tr.appendChild(td);

        return tr;
    }

    function groupRows(id) {
        return Array.prototype.filter.call(tbody.children, function (tr) {
            return tr.dataset.kelurahanFor === id;
        });
    }

    function setExpanded(row, expanded) {
        var toggle = row.querySelector('[data-kecamatan-toggle]');
        if (toggle) {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');

            var chevron = toggle.querySelector('[data-chevron]');
            // Rotasi lewat inline style supaya tidak perlu kelas rotate baru --
            // kelas transition-transform milik layout yang membuatnya beranimasi.
            if (chevron) chevron.style.transform = expanded ? 'rotate(90deg)' : '';
        }

        groupRows(row.dataset.kecamatanId).forEach(function (tr) {
            tr.hidden = !expanded;
        });
    }

    function isExpanded(row) {
        var toggle = row.querySelector('[data-kecamatan-toggle]');

        return !!toggle && toggle.getAttribute('aria-expanded') === 'true';
    }

    function toggle(row) {
        var id = row.dataset.kecamatanId;

        // Sudah pernah dimuat: cukup buka/tutup, tidak perlu request lagi.
        if (row.dataset.loaded === '1') {
            setExpanded(row, !isExpanded(row));
            return;
        }

        if (row.dataset.loading === '1') return;

        row.dataset.loading = '1';

        // Sisa percobaan sebelumnya dibersihkan supaya klik ulang setelah gagal
        // tidak menumpuk baris.
        groupRows(id).forEach(function (tr) { tr.remove(); });

        var placeholder = messageRow('Memuat…');
        placeholder.dataset.kelurahanFor = id;
        row.after(placeholder);

        window.fetch(ENDPOINT.replace('__ID__', id), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);

                return response.json();
            })
            .then(function (payload) {
                placeholder.remove();
                row.dataset.loaded = '1';

                var rows = payload.rows || [];
                var anchor = row;

                if (!rows.length) {
                    var empty = messageRow(EMPTY_TEXT);
                    empty.dataset.kelurahanFor = id;
                    anchor.after(empty);
                } else {
                    rows.forEach(function (item) {
                        var tr = childRow(item);
                        tr.dataset.kelurahanFor = id;
                        anchor.after(tr);
                        anchor = tr;
                    });
                }

                setExpanded(row, true);
            })
            .catch(function () {
                placeholder.firstElementChild.textContent = 'Gagal memuat rincian. Klik untuk mencoba lagi.';
            })
            .finally(function () {
                row.dataset.loading = '0';
            });
    }

    // Satu listener untuk seluruh tabel: klik di mana pun pada baris kecamatan
    // ikut terhitung, termasuk klik pada tombolnya (termasuk Enter/Space, karena
    // tombol mengirim event click).
    tbody.addEventListener('click', function (event) {
        var row = event.target.closest('tr[data-kecamatan-id]');
        if (!row || !tbody.contains(row)) return;

        toggle(row);
    });
})();
</script>
@endpush
