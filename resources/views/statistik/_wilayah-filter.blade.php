{{--
    Kartu filter bersama Rekap Wilayah dan Rekap Jenis Kebutuhan.

    Filternya langsung menyaring tabel rekap lewat query string, bukan menyaring
    di sisi klien: halaman dimuat ulang dengan ?kecamatan_id= dan/atau
    ?kelurahan_id=, dan controller yang menghitung ulang baris tabelnya beserta
    baris totalnya.

    Kecamatan dan kelurahan dirangkai: pilihan kelurahan mengikuti kecamatan yang
    sedang dipilih, dan sebelum kecamatan dipilih select kelurahan dikunci. Kalau
    kelurahan difilter tanpa kecamatan (mis. URL diketik tangan), controller
    menurunkan kecamatannya dari data kelurahan itu, jadi baris kecamatan tetap
    muncul di tabel.

    Parameter:
    - $action     : URL tujuan form (route halaman rekap yang bersangkutan)
    - $filters    : ['kecamatan_id' => int, 'kelurahan_id' => int]
    - $kecamatans : nama kecamatan dikunci menurut id
    - $kelurahans : daftar kelurahan per kecamatan, [kecamatan_id => [['id', 'name'], ...]]
--}}
@php
    $selectedKecamatan = (int) ($filters['kecamatan_id'] ?? 0);
    $selectedKelurahan = (int) ($filters['kelurahan_id'] ?? 0);

    // Kelurahan hanya ditampilkan setelah kecamatannya dipilih.
    $kelurahanList = $selectedKecamatan > 0 ? ($kelurahans[$selectedKecamatan] ?? []) : [];
    $hasFilter = $selectedKecamatan > 0 || $selectedKelurahan > 0;
@endphp

<div class="mb-4 rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 p-4">
        <h2 class="text-lg font-semibold text-gray-900">Filter</h2>
    </div>

    <div class="p-4">
        <form id="wilayah-filter-form" method="GET" action="{{ $action }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <label for="filter-kecamatan" class="mb-1 block text-sm font-medium text-gray-700">Kecamatan</label>
                <select id="filter-kecamatan" name="kecamatan_id" class="h-10 w-full rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none">
                    <option value="">Pilih Kecamatan</option>
                    @foreach ($kecamatans as $id => $name)
                        <option value="{{ $id }}" @selected($selectedKecamatan === (int) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label for="filter-kelurahan" class="mb-1 block text-sm font-medium text-gray-700">Kelurahan</label>
                <select id="filter-kelurahan" name="kelurahan_id" @disabled($selectedKecamatan === 0) class="h-10 w-full rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none">
                    <option value="">Pilih Kelurahan</option>
                    @foreach ($kelurahanList as $kelurahan)
                        <option value="{{ $kelurahan['id'] }}" @selected($selectedKelurahan === (int) $kelurahan['id'])>{{ $kelurahan['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex h-10 items-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-700">Terapkan</button>

                @if ($hasFilter)
                    {{-- Reset kembali ke tabel tanpa filter. --}}
                    <a href="{{ $action }}" class="inline-flex h-10 items-center rounded-md border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('wilayah-filter-form');
    var kecamatanSelect = document.getElementById('filter-kecamatan');
    var kelurahanSelect = document.getElementById('filter-kelurahan');
    if (!form || !kecamatanSelect || !kelurahanSelect) return;

    // Daftar kelurahan per kecamatan, dirender server supaya JS tidak perlu
    // request tambahan saat kecamatan berganti.
    var KELURAHANS = @json($kelurahans);

    function fillKelurahan(kecamatanId, selectedId) {
        kelurahanSelect.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih Kelurahan';
        kelurahanSelect.appendChild(placeholder);

        (KELURAHANS[kecamatanId] || []).forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            if (String(item.id) === String(selectedId)) option.selected = true;
            kelurahanSelect.appendChild(option);
        });

        // Tanpa kecamatan belum ada kelurahan yang bisa dipilih.
        kelurahanSelect.disabled = !kecamatanId;
    }

    kecamatanSelect.addEventListener('change', function () {
        // Kelurahan lama tidak berlaku lagi untuk kecamatan yang baru.
        fillKelurahan(kecamatanSelect.value, '');
        form.submit();
    });

    kelurahanSelect.addEventListener('change', function () {
        form.submit();
    });
})();
</script>
@endpush
