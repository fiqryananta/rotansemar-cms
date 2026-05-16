@php
    $title = 'Detail Tindak Lanjut';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <i class="ti ti-clipboard-text" style="font-size:1.5rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Detail Tindak Lanjut</h1>
                    </div>
                </div>

                <a href="{{ route('tindak-lanjut.index') }}" class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Kembali</a>
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
                        <h2 class="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">Informasi Pasien</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p><span class="text-gray-500">Nama:</span> <span class="font-medium text-gray-900">{{ $kebutuhan->pasien->name }}</span></p>
                            <p><span class="text-gray-500">NIK:</span> {{ $kebutuhan->pasien->nik }}</p>
                            <p><span class="text-gray-500">Alamat:</span> {{ $kebutuhan->pasien->address ?? '-' }}</p>
                            @if ($kebutuhan->pasien->catatan_kebutuhan)
                                <p><span class="text-gray-500">Catatan:</span> <span class="italic text-gray-700">{{ $kebutuhan->pasien->catatan_kebutuhan }}</span></p>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 class="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">Detail Kebutuhan</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p><span class="text-gray-500">Jenis Kebutuhan:</span> <span class="font-medium text-gray-900">{{ $kebutuhan->jenisKebutuhan->name }}</span></p>
                            <p><span class="text-gray-500">OPD:</span> {{ $kebutuhan->opd->name }}</p>
                            <p><span class="text-gray-500">Jenis Penanganan:</span> {{ $kebutuhan->jenisPenanganan->name }}</p>
                            <p><span class="text-gray-500">Uraian:</span> {{ $kebutuhan->need_detail }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-gray-900">Status Penanganan</h2>
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $isTerminal ? 'border-gray-300 bg-gray-50 text-gray-700' : 'border-indigo-300 bg-indigo-50 text-indigo-700' }}">
                            {{ $statusLabels[$kebutuhan->verification_status] ?? $kebutuhan->verification_status }}
                        </span>
                    </div>

                    @if ($isTerminal)
                        <p class="mt-3 text-sm text-gray-500">
                            {{ $kebutuhan->verification_status === 'selesai' ? 'Penanganan ini telah diselesaikan.' : 'Pasien dinyatakan tidak layak untuk penanganan ini.' }}
                        </p>
                    @endif
                </div>

                @if (! $isTerminal)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 class="mb-4 text-base font-semibold text-gray-900">Tambah Tindak Lanjut</h2>
                        <form method="POST" action="{{ route('tindak-lanjut.tambahRiwayat', $kebutuhan->id) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Update Status <span class="text-red-500">*</span></label>
                                <select id="status" name="status" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                    <option value="">-- Pilih status --</option>
                                    <option value="proses" @selected(old('status') === 'proses')>Proses</option>
                                    <option value="pending_bantuan" @selected(old('status') === 'pending_bantuan')>Pending Bantuan</option>
                                    <option value="selesai" @selected(old('status') === 'selesai')>Selesai</option>
                                    <option value="tidak_layak" @selected(old('status') === 'tidak_layak')>Tidak Layak</option>
                                </select>
                                @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan Tindak Lanjut</label>
                                <textarea id="keterangan" name="keterangan" rows="4" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('keterangan') }}</textarea>
                                @error('keterangan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Foto Bukti</label>
                                <div id="fotos-zone" class="mt-2 flex min-h-[96px] w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-center transition hover:border-indigo-400 hover:bg-indigo-50">
                                    <div id="fotos-placeholder">
                                        <i class="ti ti-upload mx-auto text-gray-400" style="font-size:2rem;" aria-hidden="true"></i>
                                        <p class="mt-2 text-sm text-gray-600"><span class="font-semibold text-indigo-600">Klik untuk memilih</span> atau seret foto ke sini</p>
                                        <p class="mt-1 text-xs text-gray-400">JPG, PNG, WEBP &middot; Minimal 1 foto</p>
                                    </div>
                                    <input id="fotos" name="fotos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" />
                                </div>
                                <div id="fotos-preview" class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5" style="display:none"></div>
                                @error('fotos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                @error('fotos.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="inline-flex h-10 items-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-700">Simpan Tindak Lanjut</button>
                        </form>
                    </div>
                @endif

                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h2 class="font-semibold text-gray-900">Riwayat Tindak Lanjut</h2>
                    </div>
                    <div class="p-4">
                        @if ($kebutuhan->tindakLanjuts->count() === 0)
                            <div class="py-10 text-center text-sm text-gray-400">Belum ada riwayat tindak lanjut.</div>
                        @else
                            <div class="space-y-4">
                                @foreach ($kebutuhan->tindakLanjuts as $item)
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $item->user?->name ?? '-' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->user?->email ?? '-' }}</p>
                                            </div>
                                            <div class="flex flex-col items-end gap-1">
                                                @if ($item->status)
                                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                                        @if ($item->status === 'selesai') bg-green-100 text-green-700
                                                        @elseif ($item->status === 'proses') bg-blue-100 text-blue-700
                                                        @elseif ($item->status === 'pending_bantuan') bg-amber-100 text-amber-700
                                                        @elseif ($item->status === 'tidak_layak') bg-red-100 text-red-700
                                                        @endif">
                                                        {{ $statusLabels[$item->status] ?? $item->status }}
                                                    </span>
                                                @endif
                                                <span class="text-sm text-gray-600">{{ $item->created_at }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-3 border-t border-gray-200 pt-3 text-sm text-gray-700 whitespace-pre-wrap">{{ $item->keterangan }}</div>
                                        @if ($item->fotos->count() > 0)
                                            <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                                @foreach ($item->fotos as $foto)
                                                    <a href="{{ $foto->url ?? url('storage/' . $foto->path) }}" data-lb-group="rw-{{ $loop->parent->index }}" class="lb-trigger block overflow-hidden rounded-md border border-gray-300 cursor-zoom-in">
                                                        <img src="{{ $foto->url ?? url('storage/' . $foto->path) }}" alt="Bukti tindak lanjut" class="aspect-square h-full w-full object-cover transition duration-150 hover:brightness-90" />
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
(function () {
    var input = document.getElementById('fotos');
    var zone  = document.getElementById('fotos-zone');
    var preview = document.getElementById('fotos-preview');
    var placeholder = document.getElementById('fotos-placeholder');
    if (!input || !zone || !preview) return;

    var files = [];

    function render() {
        preview.innerHTML = '';
        if (!files.length) {
            placeholder.style.display = '';
            preview.style.display = 'none';
            return;
        }
        placeholder.style.display = 'none';
        preview.style.display = '';
        files.forEach(function (file, i) {
            var url = URL.createObjectURL(file);
            var wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;aspect-ratio:1/1;overflow:hidden;border-radius:0.75rem;border:1px solid #e5e7eb;background:#f9fafb';
            var img = document.createElement('img');
            img.src = url; img.alt = '';
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block';
            var btn = document.createElement('button');
            btn.type = 'button'; btn.dataset.i = String(i);
            btn.innerHTML = '&times;'; btn.title = 'Hapus';
            btn.style.cssText = 'position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:#ef4444;color:#fff;font-size:15px;line-height:1;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.25)';
            wrap.appendChild(img); wrap.appendChild(btn);
            preview.appendChild(wrap);
        });
        try {
            var dt = new DataTransfer();
            files.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
        } catch (e) {}
    }

    zone.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
        files = files.concat(Array.from(input.files));
        render();
    });
    preview.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn || btn.dataset.i === undefined) return;
        files.splice(Number(btn.dataset.i), 1);
        render();
    });
    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.style.borderColor = '#6366f1';
        zone.style.background = '#eef2ff';
    });
    zone.addEventListener('dragleave', function () {
        zone.style.borderColor = '';
        zone.style.background = '';
    });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.style.borderColor = ''; zone.style.background = '';
        var dropped = Array.from(e.dataTransfer.files).filter(function (f) { return f.type.startsWith('image/'); });
        files = files.concat(dropped);
        render();
    });
})();
</script>
<script>
(function () {
    // ── Lightbox ──────────────────────────────────────────────────────────
    var ov = document.createElement('div');
    ov.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.88);align-items:center;justify-content:center;padding:16px';

    var lbImg = document.createElement('img');
    lbImg.alt = 'Foto tindak lanjut';
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
