@php($title = 'Detail Tindak Lanjut')
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 11h6"></path>
                            <path d="M9 15h6"></path>
                            <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Detail Tindak Lanjut</h1>
                        <p class="mt-2 text-gray-600">Progress penanganan pasien oleh OPD.</p>
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

                    @if (! $isTerminal)
                        <div class="mt-4 flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('tindak-lanjut.verifikasi', $kebutuhan->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="proses" />
                                <button type="submit" class="inline-flex h-10 items-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700">Proses</button>
                            </form>
                            <form method="POST" action="{{ route('tindak-lanjut.verifikasi', $kebutuhan->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="pending_bantuan" />
                                <button type="submit" class="inline-flex h-10 items-center rounded-md border border-amber-300 bg-amber-50 px-4 text-sm font-medium text-amber-700 transition hover:bg-amber-100">Pending Bantuan</button>
                            </form>
                            <form method="POST" action="{{ route('tindak-lanjut.verifikasi', $kebutuhan->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="tidak_layak" />
                                <button type="submit" class="inline-flex h-10 items-center rounded-md border border-red-300 bg-red-50 px-4 text-sm font-medium text-red-700 transition hover:bg-red-100">Tidak Layak</button>
                            </form>
                            @if ($kebutuhan->tindakLanjuts->count() > 0)
                                <form method="POST" action="{{ route('tindak-lanjut.selesai', $kebutuhan->id) }}" class="ml-auto">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex h-10 items-center rounded-md bg-green-600 px-4 text-sm font-medium text-white transition hover:bg-green-700">Selesaikan Penanganan</button>
                                </form>
                            @endif
                        </div>
                    @else
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
                                <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan Tindak Lanjut</label>
                                <textarea id="keterangan" name="keterangan" rows="4" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"></textarea>
                                @error('keterangan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="fotos" class="block text-sm font-medium text-gray-700">Foto Bukti</label>
                                <input id="fotos" name="fotos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="mt-1 block w-full text-sm" />
                                <p class="mt-1 text-xs text-gray-500">Minimal 1 foto.</p>
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
                                            <div class="text-sm text-gray-600">{{ $item->created_at }}</div>
                                        </div>
                                        <div class="mt-3 border-t border-gray-200 pt-3 text-sm text-gray-700 whitespace-pre-wrap">{{ $item->keterangan }}</div>
                                        @if ($item->fotos->count() > 0)
                                            <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                                @foreach ($item->fotos as $foto)
                                                    <a href="{{ $foto->url ?? asset('storage/' . $foto->path) }}" target="_blank" rel="noopener noreferrer" class="overflow-hidden rounded-md border border-gray-300">
                                                        <img src="{{ $foto->url ?? asset('storage/' . $foto->path) }}" alt="Bukti tindak lanjut" class="aspect-square h-full w-full object-cover" />
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
@endsection
