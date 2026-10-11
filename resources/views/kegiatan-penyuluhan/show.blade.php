@php
    $title = 'Detail Kegiatan Penyuluhan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                        <i class="ti ti-presentation" style="font-size:1.5rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Detail Kegiatan Penyuluhan</h1>
                    </div>
                </div>

                <a href="{{ route('kegiatan-penyuluhan.index') }}" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ session('error') }}</div>
            @endif

            <div class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 class="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">Informasi Kegiatan</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p><span class="text-gray-500">Nama Kegiatan:</span> <span class="font-medium text-gray-900">{{ $kegiatanPenyuluhan->nama_kegiatan }}</span></p>
                            <p><span class="text-gray-500">Tanggal:</span> {{ \Carbon\Carbon::parse($kegiatanPenyuluhan->tanggal_kegiatan)->format('d-m-Y') }}</p>
                            <p><span class="text-gray-500">Lokasi:</span> {{ $kegiatanPenyuluhan->lokasi_kegiatan }}</p>
                            <p class="flex items-center gap-1">
                                <span class="text-gray-500">Koordinat:</span>
                                <span>{{ $kegiatanPenyuluhan->koordinat_lokasi ?: '-' }}</span>
                                @if (filled($kegiatanPenyuluhan->koordinat_lokasi))
                                    <button type="button" data-no-unify data-location-view="{{ $kegiatanPenyuluhan->koordinat_lokasi }}" aria-label="Lihat lokasi di peta" title="Lihat di peta" class="location-view-trigger">
                                        <i class="ti ti-eye" style="font-size:1rem;" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 class="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">Sasaran</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p><span class="text-gray-500">Sasaran:</span> <span class="font-medium text-gray-900">{{ $kegiatanPenyuluhan->sasaran }}</span></p>
                            <p><span class="text-gray-500">Jumlah Sasaran:</span> {{ $kegiatanPenyuluhan->jumlah_sasaran }} orang</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">Uraian Kegiatan</h2>
                    <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $kegiatanPenyuluhan->uraian_kegiatan }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h2 class="font-semibold text-gray-900">Foto Kegiatan</h2>
                    </div>
                    <div class="p-4">
                        @if (empty($kegiatanPenyuluhan->foto_kegiatan))
                            <div class="py-10 text-center text-sm text-gray-400">Belum ada foto kegiatan.</div>
                        @else
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                @foreach ($kegiatanPenyuluhan->foto_kegiatan as $foto)
                                    <a href="{{ asset('storage/' . $foto) }}" data-lb-group="foto-kegiatan" class="lb-trigger block overflow-hidden rounded-md border border-gray-300 cursor-zoom-in">
                                        <img src="{{ asset('storage/' . $foto) }}" alt="Foto kegiatan" class="aspect-square h-full w-full object-cover transition duration-150 hover:brightness-90" />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.location-viewer')

@push('scripts')
<script>
(function () {
    // ── Lightbox ──────────────────────────────────────────────────────────
    // Sama seperti halaman detail tindak lanjut: klik foto untuk memperbesar,
    // lalu panah kiri/kanan atau geser untuk pindah antar foto.
    var ov = document.createElement('div');
    ov.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.88);align-items:center;justify-content:center;padding:16px';

    var lbImg = document.createElement('img');
    lbImg.alt = 'Foto kegiatan';
    lbImg.style.cssText = 'max-width:92vw;max-height:88vh;border-radius:10px;box-shadow:0 8px 40px rgba(0,0,0,.6);display:block;object-fit:contain;transition:opacity .12s ease';

    function mkBtn(html, css) {
        var b = document.createElement('button');
        b.type = 'button';
        b.innerHTML = html;
        b.style.cssText = css + ';border:none;cursor:pointer;display:flex;align-items:center;justify-content:center';
        return b;
    }

    var closeBtn = mkBtn('&times;', 'position:absolute;top:14px;right:14px;width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.18);color:#fff;font-size:26px;backdrop-filter:blur(6px)');
    var prevBtn  = mkBtn('&#8249;', 'position:absolute;left:10px;top:50%;transform:translateY(-50%);width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,.18);color:#fff;font-size:30px;backdrop-filter:blur(6px)');
    var nextBtn  = mkBtn('&#8250;', 'position:absolute;right:10px;top:50%;transform:translateY(-50%);width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,.18);color:#fff;font-size:30px;backdrop-filter:blur(6px)');
    var counter  = document.createElement('div');
    counter.style.cssText = 'position:absolute;bottom:14px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.65);font-size:12px;letter-spacing:.05em';

    ov.appendChild(lbImg);
    ov.appendChild(closeBtn);
    ov.appendChild(prevBtn);
    ov.appendChild(nextBtn);
    ov.appendChild(counter);
    document.body.appendChild(ov);

    var groups = {}, curGroup, curIdx;

    // Build groups from triggers
    document.querySelectorAll('.lb-trigger').forEach(function (el) {
        var g = el.dataset.lbGroup;
        if (!groups[g]) groups[g] = [];
        groups[g].push(el.href);
    });

    function show(g, idx) {
        curGroup = g; curIdx = idx;
        var items = groups[g];
        lbImg.src = items[idx];
        var multi = items.length > 1;
        prevBtn.style.display = multi ? 'flex' : 'none';
        nextBtn.style.display = multi ? 'flex' : 'none';
        counter.textContent   = multi ? (idx + 1) + ' / ' + items.length : '';
        ov.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function close() {
        ov.style.display = 'none';
        document.body.style.overflow = '';
    }

    function nav(dir) {
        var items = groups[curGroup];
        curIdx = (curIdx + dir + items.length) % items.length;
        lbImg.style.opacity = '0';
        setTimeout(function () {
            lbImg.src = items[curIdx];
            counter.textContent = items.length > 1 ? (curIdx + 1) + ' / ' + items.length : '';
            lbImg.style.opacity = '1';
        }, 110);
    }

    document.querySelectorAll('.lb-trigger').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            var g = el.dataset.lbGroup;
            var idx = groups[g].indexOf(el.href);
            show(g, idx >= 0 ? idx : 0);
        });
    });

    closeBtn.addEventListener('click', close);
    ov.addEventListener('click', function (e) { if (e.target === ov) close(); });
    prevBtn.addEventListener('click', function (e) { e.stopPropagation(); nav(-1); });
    nextBtn.addEventListener('click', function (e) { e.stopPropagation(); nav(1); });

    document.addEventListener('keydown', function (e) {
        if (ov.style.display === 'none') return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowLeft')  nav(-1);
        if (e.key === 'ArrowRight') nav(1);
    });

    // Touch swipe
    var tx = 0;
    lbImg.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
    lbImg.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - tx;
        if (Math.abs(dx) > 50) nav(dx > 0 ? -1 : 1);
    });
})();
</script>
@endpush
@endsection
