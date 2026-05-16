@php($title = 'Tambah Pengambilan Obat')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2v20"></path>
                        <path d="M2 12h20"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Pengambilan Obat</h1>
                    <p class="mt-2 text-gray-600">Buat jadwal dan status pengambilan obat baru.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('medication-pickups.store') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm" id="pickup-form">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="pasien_id">Pasien *</label>
                    <select id="pasien_id" name="pasien_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih Pasien</option>
                        @foreach ($pasiens as $pasien)
                            <option value="{{ $pasien->id }}" @selected(old('pasien_id') == $pasien->id)>{{ $pasien->name }} ({{ $pasien->nik }})</option>
                        @endforeach
                    </select>
                    @error('pasien_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="faskes_id">Faskes</label>
                        <select id="faskes_id" name="faskes_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                            <option value="">Pilih Faskes</option>
                            @foreach ($faskes as $item)
                                <option value="{{ $item->id }}" @selected(old('faskes_id') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('faskes_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="puskesmas_id">Puskesmas</label>
                        <select id="puskesmas_id" name="puskesmas_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                            <option value="">Pilih Puskesmas</option>
                            @foreach ($puskesmas as $item)
                                <option value="{{ $item->id }}" @selected(old('puskesmas_id') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('puskesmas_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="scheduled_date">Tanggal Jadwal *</label>
                    <input id="scheduled_date" name="scheduled_date" type="date" value="{{ old('scheduled_date') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" />
                    @error('scheduled_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="status">Status *</label>
                    <select id="status" name="status" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm">
                        <option value="">Pilih Status</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="status-fields" class="space-y-4"></div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('medication-pickups.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-blue-600 text-sm font-medium text-white transition hover:bg-blue-700">Simpan Pengambilan Obat</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const statusField = document.getElementById('status');
    const container = document.getElementById('status-fields');
    if (!statusField || !container) return;
    const render = () => {
        const status = statusField.value;
        let html = '';
        if (status === 'terrealisasi') {
            html += '<div><label class="block text-sm font-medium text-gray-700" for="actual_date">Tanggal Pengambilan Obat</label><input id="actual_date" name="actual_date" type="date" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" /><\/div>';
            html += '<div><label class="block text-sm font-medium text-gray-700" for="next_pickup_date">Jadwal Pengambilan Selanjutnya</label><input id="next_pickup_date" name="next_pickup_date" type="date" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" /><\/div>';
        } else if (status === 'pindah') {
            html += '<div><label class="block text-sm font-medium text-gray-700" for="transfer_date">Tanggal Pindah</label><input id="transfer_date" name="transfer_date" type="date" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" /><\/div>';
            html += '<div><label class="block text-sm font-medium text-gray-700" for="notes">Alasan / Keterangan</label><textarea id="notes" name="notes" rows="3" class="mt-1 min-h-24 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"><\/textarea><\/div>';
            html += '<div><label class="block text-sm font-medium text-gray-700" for="next_pickup_date">Rencana Pasien ke Faskes Tujuan</label><input id="next_pickup_date" name="next_pickup_date" type="date" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" /><\/div>';
            html += '<label class="flex items-center gap-2"><input type="checkbox" name="is_outside_city" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600"><span class="text-sm text-gray-700">Pindah ke Luar Kota</span><\/label>';
            html += '<div><label class="block text-sm font-medium text-gray-700" for="target_faskes_id">Faskes Tujuan</label><select id="target_faskes_id" name="target_faskes_id" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm"><option value="">Pilih Faskes Tujuan</option>@foreach ($faskes as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach<\/select><\/div>';
        } else if (status === 'putus_obat' || status === 'meninggal' || status === 'obat_terakhir') {
            if (status === 'obat_terakhir') {
                html += '<div><label class="block text-sm font-medium text-gray-700" for="actual_date">Tanggal Pengambilan Obat</label><input id="actual_date" name="actual_date" type="date" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm" /><\/div>';
            }
            html += '<div><label class="block text-sm font-medium text-gray-700" for="notes">Keterangan / Catatan</label><textarea id="notes" name="notes" rows="3" class="mt-1 min-h-24 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"><\/textarea><\/div>';
        }
        container.innerHTML = html;
    };
    statusField.addEventListener('change', render);
    render();
})();
</script>
@endpush
