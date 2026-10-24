{{-- Modal rincian pasien untuk tabel rekap (Rekap OPD, Rekap Wilayah).

     Dibuka dari dua pemicu di tabel rekap, keduanya bertanda data-rekap-detail:
     - nama wilayah (OPD / kecamatan / kelurahan) -> seluruh pasien di sana;
     - angka di sel -> hanya pasien dengan kebutuhan yang cocok dengan filter sel
       itu, yang ditentukan atribut data-filter-key/-value/-label (mis. status
       verifikasi atau jenis kebutuhan).

     Daftar pasiennya dimuat sekali lewat AJAX lalu dibangun di sisi klien, jadi
     membuka/menutup baris kebutuhan tidak perlu request lagi. Gaya tabel dan
     badge statusnya mengikuti halaman Tindak Lanjut supaya terbaca sama.

     Parameter: tidak ada. URL rinciannya dibaca dari atribut data-rekap-url pada
     masing-masing pemicu, karena satu halaman bisa punya dua tingkat wilayah
     (kecamatan dan kelurahan) dengan URL yang berbeda.

     Kemampuan (kelas Tailwind yang dipakai) sudah ada di bundle hasil build; lebar
     dan tinggi dialog ditulis inline karena tidak punya padanan utility. --}}
<div
    id="rekap-detail-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="rekap-detail-title"
>
    <div class="flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-xl" style="max-width:72rem;max-height:88vh;">
        <div class="flex items-start justify-between gap-3 border-b border-gray-200 px-4 py-3">
            <div class="min-w-0">
                <h2 id="rekap-detail-title" class="text-base font-semibold text-gray-900">Daftar Pasien</h2>
                <p id="rekap-detail-subtitle" class="mt-1 truncate text-sm text-gray-500">-</p>
            </div>
            <button
                type="button"
                id="rekap-detail-close"
                data-no-unify
                aria-label="Tutup"
                class="inline-flex h-9 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white px-3 text-gray-500 transition hover:bg-gray-50"
            >
                <i class="ti ti-x" style="font-size:1rem;" aria-hidden="true"></i>
            </button>
        </div>

        {{-- min-height:0 wajib saat jadi anak flex: tanpa itu area ini menolak
             menyusut dan tabel panjang mendorong footer keluar dialog. --}}
        <div class="flex-1 overflow-y-auto" style="min-height:0;">
            <div id="rekap-detail-loading" class="px-4 py-12 text-center text-sm text-gray-500">Memuat data pasien…</div>
            <div id="rekap-detail-error" class="hidden px-4 py-12 text-center text-sm text-gray-500"></div>
            <div id="rekap-detail-empty" class="hidden px-4 py-12 text-center text-sm text-gray-400">Belum ada data pasien.</div>

            <div id="rekap-detail-body" class="hidden p-4">
                <div class="overflow-x-auto rounded-md border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Pasien</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Jumlah Kebutuhan</th>
                            </tr>
                        </thead>
                        <tbody id="rekap-detail-rows" class="divide-y divide-gray-100 bg-white"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex justify-end border-t border-gray-200 px-4 py-3">
            <button type="button" id="rekap-detail-done" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var overlay = document.getElementById('rekap-detail-modal');
    var subtitleEl = document.getElementById('rekap-detail-subtitle');
    var loadingEl = document.getElementById('rekap-detail-loading');
    var errorEl = document.getElementById('rekap-detail-error');
    var emptyEl = document.getElementById('rekap-detail-empty');
    var bodyEl = document.getElementById('rekap-detail-body');
    var rowsEl = document.getElementById('rekap-detail-rows');
    var closeBtn = document.getElementById('rekap-detail-close');
    var doneBtn = document.getElementById('rekap-detail-done');

    if (!overlay || !subtitleEl || !loadingEl || !errorEl || !emptyEl || !bodyEl || !rowsEl || !closeBtn || !doneBtn) {
        return;
    }

    // Badge status disamakan dengan halaman Tindak Lanjut. 'pending' sengaja
    // tanpa warna, sama seperti di sana.
    var STATUS_BADGES = {
        pending: '',
        proses: 'bg-blue-100 text-blue-700',
        pending_bantuan: 'bg-amber-100 text-amber-700',
        tidak_layak: 'bg-red-100 text-red-700',
        selesai: 'bg-green-100 text-green-700',
    };

    var lastTrigger = null;
    // Nomor urut permintaan: kalau pengguna cepat mengklik baris lain, jawaban
    // permintaan lama yang datang belakangan tidak boleh menimpa yang baru.
    var requestSeq = 0;

    function setState(state) {
        loadingEl.classList.toggle('hidden', state !== 'loading');
        errorEl.classList.toggle('hidden', state !== 'error');
        emptyEl.classList.toggle('hidden', state !== 'empty');
        bodyEl.classList.toggle('hidden', state !== 'body');
    }

    // Teks dibuat lewat textContent, bukan innerHTML, supaya nama pasien/wilayah
    // dari database tidak bisa menyusupkan markup ke halaman.
    function cell(text, className) {
        var td = document.createElement('td');
        td.className = className;
        td.textContent = text == null || text === '' ? '-' : text;

        return td;
    }

    function kebutuhanRow(item) {
        var tr = document.createElement('tr');

        tr.appendChild(cell(item.jenis_kebutuhan, 'px-3 py-2 text-gray-700'));
        tr.appendChild(cell(item.opd, 'px-3 py-2 text-gray-700'));
        tr.appendChild(cell(item.jenis_penanganan, 'px-3 py-2 text-gray-700'));

        var statusTd = document.createElement('td');
        statusTd.className = 'px-3 py-2';
        var badge = document.createElement('span');
        badge.className = 'inline-flex rounded-full px-2 py-0.5 text-xs font-medium ' + (STATUS_BADGES[item.status_key] || '');
        badge.textContent = item.status;
        statusTd.appendChild(badge);
        tr.appendChild(statusTd);

        tr.appendChild(cell(item.tindak_lanjut_count + 'x', 'px-3 py-2 text-center text-gray-700'));

        var actionTd = document.createElement('td');
        actionTd.className = 'px-3 py-2 text-right';
        var link = document.createElement('a');
        // Kelas aksi ditulis utuh karena baris ini dibuat setelah penyeragam
        // tombol di layout berjalan.
        link.className = 'ui-action-btn ui-action-btn--detail';
        link.href = item.url;
        var icon = document.createElement('i');
        icon.className = 'ti ti-eye ui-action-icon';
        icon.setAttribute('aria-hidden', 'true');
        link.appendChild(icon);
        link.appendChild(document.createTextNode('Detail'));
        actionTd.appendChild(link);
        tr.appendChild(actionTd);

        return tr;
    }

    // Tabel kebutuhan pasien, dibuat sebagai tabel bersarang di dalam satu sel
    // supaya keenam kolomnya punya lebar penuh, tidak terpaksa lurus dengan dua
    // kolom tabel pasien di atasnya.
    function kebutuhanTable(items) {
        var wrapper = document.createElement('div');
        wrapper.className = 'overflow-x-auto rounded-md border border-gray-200 bg-white';

        var table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200 text-sm';

        var thead = document.createElement('thead');
        thead.className = 'bg-gray-50';
        var headRow = document.createElement('tr');
        [
            ['Jenis Kebutuhan', 'px-3 py-2 text-left font-semibold text-gray-700'],
            ['OPD', 'px-3 py-2 text-left font-semibold text-gray-700'],
            ['Jenis Penanganan', 'px-3 py-2 text-left font-semibold text-gray-700'],
            ['Status', 'px-3 py-2 text-left font-semibold text-gray-700'],
            ['Tindak Lanjut', 'px-3 py-2 text-center font-semibold text-gray-700'],
            ['Aksi', 'px-3 py-2 text-right font-semibold text-gray-700'],
        ].forEach(function (pair) {
            var th = document.createElement('th');
            th.className = pair[1];
            th.textContent = pair[0];
            headRow.appendChild(th);
        });
        thead.appendChild(headRow);
        table.appendChild(thead);

        var tbody = document.createElement('tbody');
        tbody.className = 'divide-y divide-gray-100';
        items.forEach(function (item) { tbody.appendChild(kebutuhanRow(item)); });
        table.appendChild(tbody);

        wrapper.appendChild(table);

        return wrapper;
    }

    function patientRows(patient) {
        var row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';

        var nameTd = document.createElement('td');
        nameTd.className = 'px-4 py-3';
        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'inline-flex items-center gap-2 text-left font-medium text-gray-900';
        toggle.setAttribute('aria-expanded', 'false');

        var chevron = document.createElement('i');
        chevron.className = 'ti ti-chevron-right text-gray-400 transition-transform duration-200';
        chevron.style.fontSize = '1rem';
        chevron.setAttribute('aria-hidden', 'true');
        chevron.setAttribute('data-chevron', '');
        toggle.appendChild(chevron);

        var nameWrap = document.createElement('span');
        nameWrap.appendChild(document.createTextNode(patient.name));
        var nik = document.createElement('span');
        nik.className = 'block text-xs text-gray-500';
        nik.textContent = patient.nik || '';
        nameWrap.appendChild(nik);
        toggle.appendChild(nameWrap);
        nameTd.appendChild(toggle);

        var countTd = cell(patient.kebutuhans.length, 'px-4 py-3 text-center font-semibold text-gray-900');

        row.appendChild(nameTd);
        row.appendChild(countTd);

        var detailRow = document.createElement('tr');
        detailRow.hidden = true;
        var detailTd = document.createElement('td');
        detailTd.colSpan = 2;
        detailTd.className = 'bg-sky-50 px-4 py-3';
        detailTd.appendChild(kebutuhanTable(patient.kebutuhans));
        detailRow.appendChild(detailTd);

        toggle.addEventListener('click', function () {
            var expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            detailRow.hidden = expanded;
            chevron.style.transform = expanded ? '' : 'rotate(90deg)';
        });

        return [row, detailRow];
    }

    function render(payload) {
        rowsEl.innerHTML = '';

        var patients = payload.patients || [];

        if (!patients.length) {
            setState('empty');
            return;
        }

        patients.forEach(function (patient) {
            patientRows(patient).forEach(function (tr) { rowsEl.appendChild(tr); });
        });

        setState('body');
    }

    function open(trigger) {
        lastTrigger = trigger;

        // Label filter ditulis apa adanya, mis. 'Status: Selesai'.
        var filterLabel = trigger.dataset.filterLabel || '';
        subtitleEl.textContent = trigger.dataset.rekapName
            + ' · ' + (filterLabel || 'Semua data');

        setState('loading');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        var seq = ++requestSeq;
        var url = trigger.dataset.rekapUrl;

        if (!url) {
            errorEl.textContent = 'Alamat rincian tidak ditemukan pada baris ini.';
            setState('error');
            return;
        }

        if (trigger.dataset.filterKey && trigger.dataset.filterValue) {
            // Filter dikirim sebagai query string, menimpa kalau URL-nya sudah
            // punya query sendiri.
            url += (url.indexOf('?') === -1 ? '?' : '&')
                + encodeURIComponent(trigger.dataset.filterKey) + '=' + encodeURIComponent(trigger.dataset.filterValue);
        }

        window.fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);

                return response.json();
            })
            .then(function (payload) {
                if (seq !== requestSeq) return;

                subtitleEl.textContent = payload.title
                    + ' · ' + (payload.filter_label || 'Semua data');
                render(payload);
            })
            .catch(function () {
                if (seq !== requestSeq) return;

                errorEl.textContent = 'Gagal memuat data pasien. Tutup lalu coba lagi.';
                setState('error');
            });
    }

    var close = function () {
        // Jawaban yang masih di jalan diabalkan supaya tidak mengubah isi modal
        // yang sudah ditutup.
        requestSeq++;
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');

        if (lastTrigger) {
            lastTrigger.focus();
        }
    };

    // Satu listener untuk seluruh halaman, bukan per elemen: pemicu di baris
    // kelurahan baru dibuat setelah drilldown selesai (AJAX), jadi listener yang
    // dipasang saat halaman dimuat tidak akan pernah menempel di sana.
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-rekap-detail]');
        if (!trigger) return;

        event.preventDefault();
        open(trigger);
    });

    closeBtn.addEventListener('click', close);
    doneBtn.addEventListener('click', close);

    overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
            close();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
            close();
        }
    });
})();
</script>
@endpush
