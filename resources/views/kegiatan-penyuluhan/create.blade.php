@php
    $title = 'Tambah Kegiatan Penyuluhan';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                    <i class="ti ti-presentation" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Kegiatan Penyuluhan</h1>
                    <p class="mt-2 text-gray-600">Isi detail kegiatan dan unggah foto kegiatan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('kegiatan-penyuluhan.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="nama_kegiatan">Nama Kegiatan</label>
                        <input id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('nama_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="tanggal_kegiatan">Tanggal Kegiatan</label>
                        <input id="tanggal_kegiatan" name="tanggal_kegiatan" type="date" value="{{ old('tanggal_kegiatan') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('tanggal_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="sasaran">Sasaran</label>
                        <input id="sasaran" name="sasaran" value="{{ old('sasaran') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('sasaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="jumlah_sasaran">Jumlah Sasaran</label>
                        <input id="jumlah_sasaran" name="jumlah_sasaran" type="number" min="1" value="{{ old('jumlah_sasaran') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('jumlah_sasaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="lokasi_kegiatan">Lokasi Kegiatan</label>
                        <input id="lokasi_kegiatan" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                        @error('lokasi_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="koordinat_lokasi">Koordinat Lokasi</label>
                        <input id="koordinat_lokasi" name="koordinat_lokasi" value="{{ old('koordinat_lokasi') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" placeholder="Lat, Long" />
                        <p class="mt-1 text-xs text-gray-500">Boleh dikosongkan.</p>
                        @error('koordinat_lokasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="uraian_kegiatan">Uraian Kegiatan</label>
                    <textarea id="uraian_kegiatan" name="uraian_kegiatan" rows="4" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('uraian_kegiatan') }}</textarea>
                    @error('uraian_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Foto Kegiatan</label>
                    <div id="fk-zone" class="mt-2 flex min-h-[96px] w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-center transition hover:border-cyan-400 hover:bg-cyan-50">
                        <div id="fk-placeholder">
                            <i class="ti ti-upload mx-auto text-gray-400" style="font-size:2rem;" aria-hidden="true"></i>
                            <p class="mt-2 text-sm text-gray-600"><span class="font-semibold text-cyan-600">Klik untuk memilih</span> atau seret foto ke sini</p>
                            <p class="mt-1 text-xs text-gray-400">JPG, PNG, WEBP &middot; Bisa lebih dari 1 foto</p>
                        </div>
                        <input id="foto_kegiatan" name="foto_kegiatan[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" />
                    </div>
                    <div id="fk-preview" class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5" style="display:none"></div>
                    @error('foto_kegiatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('foto_kegiatan.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('kegiatan-penyuluhan.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-cyan-600 text-sm font-medium text-white transition hover:bg-cyan-700">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
@push('scripts')
<script>
(function () {
    var input = document.getElementById('foto_kegiatan');
    var zone  = document.getElementById('fk-zone');
    var preview = document.getElementById('fk-preview');
    var placeholder = document.getElementById('fk-placeholder');
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
        zone.style.borderColor = '#06b6d4';
        zone.style.background = '#ecfeff';
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
@endpush
@endsection
