@php
    $pasien = $pasien ?? null;
    $value = function (string $key, $default = '') use ($pasien) {
        return old($key, data_get($pasien, $key, $default));
    };
    $boolValue = function (string $key, $default = false) use ($pasien) {
        return old($key, data_get($pasien, $key, $default));
    };
    $nullableBoolValue = function (string $key) use ($pasien) {
        return old($key, data_get($pasien, $key));
    };
    $kebutuhanOld = old('kebutuhans');
    if (is_array($kebutuhanOld) && count($kebutuhanOld) > 0) {
        $kebutuhanRows = $kebutuhanOld;
    } elseif ($pasien && $pasien->relationLoaded('kebutuhans') && $pasien->kebutuhans->count() > 0) {
        $kebutuhanRows = $pasien->kebutuhans->map(function ($item) {
            return [
                'jenis_kebutuhan_id' => $item->jenis_kebutuhan_id,
                'opd_id' => $item->opd_id,
                'jenis_penanganan_id' => $item->jenis_penanganan_id,
                'need_detail' => $item->need_detail,
            ];
        })->all();
    } else {
        $kebutuhanRows = [[
            'jenis_kebutuhan_id' => '',
            'opd_id' => '',
            'jenis_penanganan_id' => '',
            'need_detail' => '',
        ]];
    }

    $selectYesNo = [
        '' => 'Pilih',
        '1' => 'Ya',
        '0' => 'Tidak',
    ];

    $selectNullableYesNo = [
        '' => 'Pilih',
        '1' => 'Ya',
        '0' => 'Tidak',
    ];

    // Daftar opsi Ya/Tidak untuk satu kolom. Kolom sanitasi dulunya input teks bebas,
    // jadi kalau masih ada isi lama di luar Ya/Tidak, nilai itu ditambahkan sebagai
    // satu opsi tambahan supaya tidak terhapus diam-diam begitu form disimpan.
    $yesNoOptions = function (string $field) use ($selectNullableYesNo, $nullableBoolValue) {
        $options = $selectNullableYesNo;
        $raw = (string) $nullableBoolValue($field);

        if (!array_key_exists($raw, $options)) {
            $options[$raw] = $raw;
        }

        return $options;
    };

    $selectHouseStatus = [
        '' => 'Pilih',
        'Layak Huni' => 'Layak Huni',
        'Tidak Layak Huni' => 'Tidak Layak Huni',
    ];

    // Alasannya sama dengan $yesNoOptions: kolom house_status dulunya input teks
    // bebas, jadi nilai lama di luar kedua pilihan itu tetap ditampilkan sebagai
    // satu opsi tambahan supaya tidak terhapus diam-diam begitu form disimpan.
    $houseStatusOptions = function () use ($selectHouseStatus, $value) {
        $options = $selectHouseStatus;
        $raw = (string) $value('house_status');

        if (!array_key_exists($raw, $options)) {
            $options[$raw] = $raw;
        }

        return $options;
    };
@endphp

<div class="space-y-6">
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">Periksa kembali isian berikut:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-6" id="pasien-form">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="grid gap-2 rounded-lg border border-gray-200 bg-white p-2 shadow-sm sm:grid-cols-2 lg:grid-cols-4" id="pasien-tab-nav">
            <button type="button" data-tab-target="0" class="rounded-md border border-cyan-600 bg-cyan-600 px-3 py-2 text-sm font-semibold text-white transition">Identitas</button>
            <button type="button" data-tab-target="1" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition">Riwayat Kesehatan</button>
            <button type="button" data-tab-target="2" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition">Data Lainnya</button>
            <button type="button" data-tab-target="3" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition">Kebutuhan</button>
        </div>

        <div id="pasien-tab-panels" class="space-y-6">
            <div class="space-y-6" data-tab-panel="0">

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Data Pasien</h2>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="name">Nama Pasien</label>
                    <input id="name" name="name" value="{{ $value('name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Nama Pasien" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="nik">NIK</label>
                    <input id="nik" name="nik" value="{{ $value('nik') }}" maxlength="16" inputmode="numeric" pattern="[0-9]*" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan NIK" />
                    @error('nik')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="birth_date">Tanggal Lahir</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ $value('birth_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Tanggal Lahir" />
                    @error('birth_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="laki-laki" @selected($value('gender') === 'laki-laki')>Laki-laki</option>
                        <option value="perempuan" @selected($value('gender') === 'perempuan')>Perempuan</option>
                    </select>
                    @error('gender')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="faskes_id">Faskes</label>
                    <select id="faskes_id" name="faskes_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Faskes</option>
                        @foreach ($faskes as $item)
                            <option value="{{ $item->id }}" @selected((string) $value('faskes_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('faskes_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="puskesmas_id">Puskesmas</label>
                    <select id="puskesmas_id" name="puskesmas_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Puskesmas</option>
                        @foreach ($puskesmas as $item)
                            <option value="{{ $item->id }}" @selected((string) $value('puskesmas_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('puskesmas_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="kecamatan_id">Kecamatan</label>
                    <select id="kecamatan_id" name="kecamatan_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Kecamatan</option>
                        @foreach ($kecamatans as $item)
                            <option value="{{ $item->id }}" @selected((string) $value('kecamatan_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('kecamatan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="kelurahan_id">Kelurahan</label>
                    <select id="kelurahan_id" name="kelurahan_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Kelurahan</option>
                        @foreach ($kelurahans as $item)
                            <option value="{{ $item->id }}" data-kecamatan="{{ $item->kecamatan_id }}" @selected((string) $value('kelurahan_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('kelurahan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="coordinates-picker">Koordinat</label>
                    {{-- Nilai tetap dikirim lewat input hidden, tapi tidak bisa diketik lagi:
                         satu-satunya cara mengisinya adalah lewat peta, supaya formatnya konsisten
                         "lat, lng" dan tidak ada koordinat yang salah tulis. --}}
                    <input type="hidden" id="coordinates" name="coordinates" value="{{ $value('coordinates') }}" />
                    <button
                        type="button"
                        id="coordinates-picker"
                        data-no-unify
                        class="mt-1 inline-flex h-10 w-full items-center gap-2 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-700 transition hover:bg-gray-50"
                    >
                        <i class="ti ti-map-pin shrink-0 text-gray-400" style="font-size:1rem;" aria-hidden="true"></i>
                        <span id="coordinates-label" class="truncate">{{ $value('coordinates') ?: 'Pilih Lokasi' }}</span>
                    </button>
                    @error('coordinates')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="weight">Berat Badan</label>
                    {{-- Satuannya ditulis sebagai suffix di dalam kotak isian, jadi teksnya
                         perlu diberi jarak kanan (pr-10) supaya tidak tertimpa. Panah
                         naik/turun input angka sudah disembunyikan lewat aturan
                         #pasien-form di app.css. --}}
                    <div class="relative mt-1">
                        <input id="weight" name="weight" type="number" min="0" value="{{ $value('weight') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 pr-10 text-sm" placeholder="Masukkan Berat Badan" />
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">kg</span>
                    </div>
                    @error('weight')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="height">Tinggi Badan</label>
                    <div class="relative mt-1">
                        <input id="height" name="height" type="number" min="0" value="{{ $value('height') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 pr-10 text-sm" placeholder="Masukkan Tinggi Badan" />
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">cm</span>
                    </div>
                    @error('height')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="address">Alamat KTP</label>
                    <textarea id="address" name="address" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Alamat KTP">{{ $value('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="new_address">Alamat Domisili</label>
                    <textarea id="new_address" name="new_address" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Alamat Domisili">{{ $value('new_address') }}</textarea>
                    @error('new_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="ever_received_assistance">Pernah Mendapatkan Bantuan</label>
                    <input type="hidden" name="ever_received_assistance" value="0" />
                    <input id="ever_received_assistance" name="ever_received_assistance" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('ever_received_assistance')) />
                    @error('ever_received_assistance')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="willing_to_help">Bersedia Dibantu</label>
                    <input type="hidden" name="willing_to_help" value="0" />
                    <input id="willing_to_help" name="willing_to_help" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('willing_to_help')) />
                    @error('willing_to_help')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Pekerjaan & Keluarga</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="economic_status">Status Ekonomi</label>
                    <select id="economic_status" name="economic_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Status Ekonomi</option>
                        <option value="miskin" @selected($value('economic_status') === 'miskin')>Miskin</option>
                        <option value="sederhana" @selected($value('economic_status') === 'sederhana')>Sederhana</option>
                        <option value="mampu" @selected($value('economic_status') === 'mampu')>Mampu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="pekerjaan_id">Pekerjaan</label>
                    <select id="pekerjaan_id" name="pekerjaan_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Pekerjaan</option>
                        @foreach ($pekerjaan as $item)
                            <option value="{{ $item->id }}" @selected((string) $value('pekerjaan_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="workplace_name">Nama Tempat Bekerja</label>
                    <input id="workplace_name" name="workplace_name" value="{{ $value('workplace_name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Nama Tempat Bekerja" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="workplace_address">Alamat Tempat Bekerja</label>
                    <input id="workplace_address" name="workplace_address" value="{{ $value('workplace_address') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Alamat Tempat Bekerja" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_head_name">Nama Kepala Keluarga</label>
                    <input id="family_head_name" name="family_head_name" value="{{ $value('family_head_name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Nama Kepala Keluarga" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_head_nik">NIK Kepala Keluarga</label>
                    <input id="family_head_nik" name="family_head_nik" maxlength="16" value="{{ $value('family_head_nik') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan NIK Kepala Keluarga" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_head_pekerjaan_id">Pekerjaan Kepala Keluarga</label>
                    <select id="family_head_pekerjaan_id" name="family_head_pekerjaan_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Pekerjaan</option>
                        @foreach ($pekerjaan as $item)
                            <option value="{{ $item->id }}" @selected((string) $value('family_head_pekerjaan_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="parent_marital_status">Status Perkawinan Orang Tua</label>
                    <select id="parent_marital_status" name="parent_marital_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Status Perkawinan Orang Tua</option>
                        <option value="menikah" @selected($value('parent_marital_status') === 'menikah')>Menikah</option>
                        <option value="cerai" @selected($value('parent_marital_status') === 'cerai')>Cerai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="parenting_pattern">Pola Asuh</label>
                    <input id="parenting_pattern" name="parenting_pattern" value="{{ $value('parenting_pattern') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Pola Asuh" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_income_range">Rentang Pendapatan</label>
                    <select id="family_income_range" name="family_income_range" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Rentang Pendapatan</option>
                        <option value="&lt;1000000" @selected($value('family_income_range') === '<1000000')>&lt;1000000</option>
                        <option value="&gt;1000000" @selected($value('family_income_range') === '>1000000')>&gt;1000000</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="respondent">Responden</label>
                    <input type="hidden" name="respondent" value="0" />
                    <input id="respondent" name="respondent" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('respondent')) />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="patient_relationship">Hubungan dengan Pasien</label>
                    <select id="patient_relationship" name="patient_relationship" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Hubungan dengan Pasien</option>
                        <option value="pasien-sendiri" @selected($value('patient_relationship') === 'pasien-sendiri')>Pasien Sendiri</option>
                        <option value="orang-tua" @selected($value('patient_relationship') === 'orang-tua')>Orang Tua</option>
                        <option value="anak" @selected($value('patient_relationship') === 'anak')>Anak</option>
                    </select>
                </div>
            </div>
        </section>

            </div>

            <div class="hidden space-y-6" data-tab-panel="1">

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Riwayat Kesehatan</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div><label class="block text-sm font-medium text-gray-700" for="tb_so_ro">TB SO/RO</label><input id="tb_so_ro" name="tb_so_ro" type="number" min="0" value="{{ $value('tb_so_ro') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan TB SO/RO" /></div>
                <div><label class="block text-sm font-medium text-gray-700" for="treatment_start_date">Tanggal Mulai Pengobatan</label><input id="treatment_start_date" name="treatment_start_date" type="date" value="{{ $value('treatment_start_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Tanggal Mulai Pengobatan" /></div>
                <div><label class="block text-sm font-medium text-gray-700" for="visit_date">Tanggal Kunjungan</label><input id="visit_date" name="visit_date" type="date" value="{{ $value('visit_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Tanggal Kunjungan" /></div>
                <div><label class="block text-sm font-medium text-gray-700" for="information_date">Tanggal Informasi</label><input id="information_date" name="information_date" type="date" value="{{ $value('information_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Tanggal Informasi" /></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="treatment_status">Status Pengobatan</label>
                    <select id="treatment_status" name="treatment_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Status Pengobatan</option>
                        <option value="terlaksana" @selected($value('treatment_status') === 'terlaksana')>Terlaksana</option>
                        <option value="belum" @selected($value('treatment_status') === 'belum')>Belum</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700" for="transmission_source">Sumber Penularan</label><input id="transmission_source" name="transmission_source" value="{{ $value('transmission_source') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Sumber Penularan" /></div>
                <div class="md:col-span-2 lg:col-span-3"><label class="block text-sm font-medium text-gray-700" for="follow_up_plan">Rencana Tindak Lanjut</label><textarea id="follow_up_plan" name="follow_up_plan" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Rencana Tindak Lanjut">{{ $value('follow_up_plan') }}</textarea></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="pregnancy_status">Status Kehamilan</label>
                    <input type="hidden" name="pregnancy_status" value="0" />
                    <input id="pregnancy_status" name="pregnancy_status" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('pregnancy_status')) />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="comorbid_status">Status Komorbid</label>
                    <input type="hidden" name="comorbid_status" value="0" />
                    <input id="comorbid_status" name="comorbid_status" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('comorbid_status')) />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="smoking_behavior">Perilaku Merokok</label>
                    <input type="hidden" name="smoking_behavior" value="0" />
                    <input id="smoking_behavior" name="smoking_behavior" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('smoking_behavior')) />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_smoking_status">Keluarga Merokok</label>
                    <input type="hidden" name="family_smoking_status" value="0" />
                    <input id="family_smoking_status" name="family_smoking_status" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('family_smoking_status')) />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="immunization_status">Status Imunisasi</label>
                    <select id="immunization_status" name="immunization_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Status Imunisasi</option>
                        <option value="lengkap" @selected($value('immunization_status') === 'lengkap')>Lengkap</option>
                        <option value="tidak-lengkap" @selected($value('immunization_status') === 'tidak-lengkap')>Tidak Lengkap</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="nutritional_status">Status Gizi</label>
                    <select id="nutritional_status" name="nutritional_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Status Gizi</option>
                        <option value="normal" @selected($value('nutritional_status') === 'normal')>Normal</option>
                        <option value="tidak-normal" @selected($value('nutritional_status') === 'tidak-normal')>Tidak Normal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="jkn_ownership">Kepemilikan JKN</label>
                    <input type="hidden" name="jkn_ownership" value="0" />
                    <input id="jkn_ownership" name="jkn_ownership" type="checkbox" value="1" class="mt-2 h-4 w-4 rounded border-gray-300" @checked((bool) $boolValue('jkn_ownership')) />
                </div>
            </div>
        </section>

            </div>

            <div class="hidden space-y-6" data-tab-panel="2">

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Kondisi Rumah</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'home_area' => 'Area Rumah',
                    'house_area' => 'Luas Rumah',
                    'house_type' => 'Tipe Rumah',
                    'house_status' => 'Status Rumah',
                    'home_lighting' => 'Pencahayaan',
                    'home_humidity' => 'Kelembapan',
                    'home_cleanliness' => 'Kebersihan Rumah',
                    'home_floor' => 'Lantai',
                    'home_ventilation' => 'Ventilasi',
                    'home_ceiling' => 'Plafon',
                    'home_ceiling_condition' => 'Kondisi Plafon',
                    'home_wall' => 'Dinding',
                    'home_bedroom_window' => 'Jendela Kamar',
                    'home_family_room_window' => 'Jendela Ruang Keluarga',
                    'home_kitchen_smoke_hole' => 'Lubang Asap Dapur',
                    'home_open_family_room_window' => 'Jendela Ruang Keluarga Terbuka',
                    'home_clean_house_habit' => 'Kebiasaan Membersihkan Rumah',
                ] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="{{ $field }}">{{ $label }}</label>
                        @if ($field === 'house_status')
                            {{-- Status Rumah dipilih dari dua nilai baku, bukan teks bebas. --}}
                            <select id="{{ $field }}" name="{{ $field }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                @foreach ($houseStatusOptions() as $key => $optionLabel)
                                    <option value="{{ $key }}" @selected((string) $value($field) === (string) $key)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $field }}" name="{{ $field }}" value="{{ $value($field) }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan {{ $label }}" />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Sanitasi & Hewan</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'sanitation_clean_water' => 'Air Bersih',
                    'sanitation_toilet' => 'Toilet',
                    'sanitation_wastewater_disposal' => 'Pembuangan Air Limbah',
                    'sanitation_garbage_water_disposal' => 'Pembuangan Sampah',
                    'sanitation_trash' => 'Tempat Sampah',
                    'sanitation_feces_disposal' => 'Pembuangan Feses',
                    'sanitation_throw_trash_habit' => 'Kebiasaan Membuang Sampah',
                    'sanitation_handwashing_habit' => 'Kebiasaan Cuci Tangan',
                ] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="{{ $field }}">{{ $label }}</label>
                        <select id="{{ $field }}" name="{{ $field }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            @foreach ($yesNoOptions($field) as $key => $optionLabel)
                                <option value="{{ $key }}" @selected((string) $nullableBoolValue($field) === (string) $key)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="has_livestock">Punya Ternak</label>
                    <select id="has_livestock" name="has_livestock" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        @foreach ($selectNullableYesNo as $key => $label)
                            <option value="{{ $key }}" @selected((string) $nullableBoolValue('has_livestock') === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="has_animal_cage">Punya Kandang Hewan</label>
                    <select id="has_animal_cage" name="has_animal_cage" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        @foreach ($selectNullableYesNo as $key => $label)
                            <option value="{{ $key }}" @selected((string) $nullableBoolValue('has_animal_cage') === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

            </div>

            <div class="hidden space-y-6" data-tab-panel="3">

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Catatan & Kebutuhan</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="catatan_kebutuhan">Catatan Kebutuhan</label>
                    <textarea id="catatan_kebutuhan" name="catatan_kebutuhan" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Masukkan Catatan Kebutuhan">{{ $value('catatan_kebutuhan') }}</textarea>
                </div>

                <div id="kebutuhan-list" class="space-y-4">
                    @foreach ($kebutuhanRows as $index => $row)
                        <div class="kebutuhan-row rounded-lg border border-gray-200 bg-gray-50 p-4" data-row-index="{{ $index }}">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="font-semibold text-gray-900">Kebutuhan #{{ $index + 1 }}</h3>
                                <button type="button" class="remove-kebutuhan inline-flex h-9 items-center rounded-md border border-red-300 bg-red-50 px-3 text-sm font-medium text-red-700 hover:bg-red-100">Hapus</button>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jenis Kebutuhan</label>
                                    <select name="kebutuhans[{{ $index }}][jenis_kebutuhan_id]" class="jenis-kebutuhan mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih Jenis Kebutuhan</option>
                                        @foreach ($jenisKebutuhans as $item)
                                            <option value="{{ $item->id }}" @selected((string) data_get($row, 'jenis_kebutuhan_id', '') === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("kebutuhans.$index.jenis_kebutuhan_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">OPD</label>
                                    <select name="kebutuhans[{{ $index }}][opd_id]" class="opd-id mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih OPD</option>
                                        @foreach ($opds as $item)
                                            <option value="{{ $item->id }}" @selected((string) data_get($row, 'opd_id', '') === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("kebutuhans.$index.opd_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jenis Penanganan</label>
                                    <select name="kebutuhans[{{ $index }}][jenis_penanganan_id]" class="jenis-penanganan mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih Jenis Penanganan</option>
                                        @foreach ($jenisPenanganans as $item)
                                            <option value="{{ $item->id }}" data-opds='@json($item->opds->pluck("id"))' data-kebutuhans='@json($item->jenisKebutuhans->pluck("id"))' @selected((string) data_get($row, 'jenis_penanganan_id', '') === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("kebutuhans.$index.jenis_penanganan_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Detail Kebutuhan</label>
                                    <textarea name="kebutuhans[{{ $index }}][need_detail]" rows="3" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm" placeholder="Masukkan Detail Kebutuhan">{{ data_get($row, 'need_detail', '') }}</textarea>
                                    @error("kebutuhans.$index.need_detail")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-kebutuhan" class="inline-flex h-10 items-center rounded-md border border-cyan-300 bg-cyan-50 px-4 text-sm font-medium text-cyan-700 hover:bg-cyan-100">Tambah Kebutuhan</button>
            </div>
        </section>

            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:items-center">
            <div class="flex gap-3 sm:ml-auto">
                <button type="button" id="tab-prev" class="inline-flex h-10 items-center rounded-md border border-red-300 bg-red-50 px-4 text-sm font-medium text-red-700 transition hover:bg-red-100">Kembali</button>
                <button type="button" id="tab-next" class="inline-flex h-10 items-center rounded-md border border-cyan-300 bg-cyan-50 px-4 text-sm font-medium text-cyan-700 transition hover:bg-cyan-100">Selanjutnya</button>
                <button type="submit" id="tab-submit" class="hidden inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 text-sm font-medium text-white hover:bg-cyan-700">{{ $submitLabel }}</button>
            </div>
        </div>
    </form>
</div>

{{-- Modal peta pemilih lokasi. Sengaja ditaruh di luar <form> supaya tombol di dalamnya
     tidak ikut ter-submit, dan tombolnya diberi type="button" sebagai pengaman ganda.
     Semua kelas Tailwind di sini sudah ada di bundle hasil build, jadi tidak perlu
     menjalankan `npm run build`; dua ukuran yang tidak punya padanan utility
     (lebar dialog & tinggi peta) ditulis inline. --}}
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

<template id="kebutuhan-template">
    <div class="kebutuhan-row rounded-lg border border-gray-200 bg-gray-50 p-4" data-row-index="__INDEX__">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-900">Kebutuhan #__NUMBER__</h3>
            <button type="button" class="remove-kebutuhan inline-flex h-9 items-center rounded-md border border-red-300 bg-red-50 px-3 text-sm font-medium text-red-700 hover:bg-red-100">Hapus</button>
        </div>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Kebutuhan</label>
                <select name="kebutuhans[__INDEX__][jenis_kebutuhan_id]" class="jenis-kebutuhan mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih Jenis Kebutuhan</option>
                    @foreach ($jenisKebutuhans as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">OPD</label>
                <select name="kebutuhans[__INDEX__][opd_id]" class="opd-id mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih OPD</option>
                    @foreach ($opds as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Penanganan</label>
                <select name="kebutuhans[__INDEX__][jenis_penanganan_id]" class="jenis-penanganan mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih Jenis Penanganan</option>
                    @foreach ($jenisPenanganans as $item)
                        <option value="{{ $item->id }}" data-opds='@json($item->opds->pluck("id"))' data-kebutuhans='@json($item->jenisKebutuhans->pluck("id"))'>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Detail Kebutuhan</label>
                <textarea name="kebutuhans[__INDEX__][need_detail]" rows="3" class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm" placeholder="Masukkan Detail Kebutuhan"></textarea>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const tabPanels = Array.from(document.querySelectorAll('[data-tab-panel]'));
    const tabButtons = Array.from(document.querySelectorAll('[data-tab-target]'));
    const prevButton = document.getElementById('tab-prev');
    const nextButton = document.getElementById('tab-next');
    const submitButton = document.getElementById('tab-submit');

    const setTabButtonState = (button, active) => {
        button.classList.toggle('bg-cyan-600', active);
        button.classList.toggle('text-white', active);
        button.classList.toggle('border-cyan-600', active);
        button.classList.toggle('font-semibold', active);

        button.classList.toggle('bg-white', !active);
        button.classList.toggle('text-gray-700', !active);
        button.classList.toggle('border-gray-200', !active);
        button.classList.toggle('font-medium', !active);
    };

    let currentTab = 0;
    const setActiveTab = (targetIndex) => {
        if (!tabPanels.length) {
            return;
        }

        currentTab = Math.min(Math.max(targetIndex, 0), tabPanels.length - 1);

        tabPanels.forEach((panel, index) => {
            panel.classList.toggle('hidden', index !== currentTab);
        });

        tabButtons.forEach((button, index) => {
            const active = index === currentTab;
            setTabButtonState(button, active);
            button.setAttribute('aria-current', active ? 'step' : 'false');
        });

        const isFirst = currentTab === 0;
        const isLast = currentTab === tabPanels.length - 1;

        if (prevButton) {
            prevButton.disabled = isFirst;
            prevButton.classList.toggle('opacity-50', isFirst);
            prevButton.classList.toggle('cursor-not-allowed', isFirst);
        }
        if (nextButton) {
            nextButton.classList.toggle('hidden', isLast);
        }
        if (submitButton) {
            submitButton.classList.toggle('hidden', !isLast);
        }
    };

    if (tabPanels.length) {
        const initialTab = tabPanels.findIndex((panel) => panel.querySelector('.text-red-600'));
        setActiveTab(initialTab >= 0 ? initialTab : 0);
    }

    tabButtons.forEach((button, index) => {
        button.addEventListener('click', () => setActiveTab(index));
    });

    if (prevButton) {
        prevButton.addEventListener('click', () => setActiveTab(currentTab - 1));
    }

    if (nextButton) {
        nextButton.addEventListener('click', () => setActiveTab(currentTab + 1));
    }

    /*
     * Kecamatan -> Kelurahan bertingkat: pilihan kelurahan mengikuti kecamatan
     * yang dipilih. Daftar <option> dibangun ulang, bukan sekadar ditandai
     * `hidden`, karena Select2 menyusun dropdown dari elemen <option> dan
     * mengabaikan `hidden`. Perubahan daftar option otomatis memicu refresh
     * Select2 lewat MutationObserver di layout.
     */
    const kelurahanSelect = document.getElementById('kelurahan_id');
    const kecamatanSelect = document.getElementById('kecamatan_id');
    if (kelurahanSelect && kecamatanSelect) {
        const kelurahanPlaceholder = kelurahanSelect.querySelector('option[value=""]');
        const allKelurahans = Array.from(kelurahanSelect.querySelectorAll('option'))
            .filter((option) => option.value)
            .map((option) => ({
                value: option.value,
                label: option.textContent,
                kecamatan: option.dataset.kecamatan,
            }));

        let userChangedKecamatan = false;

        const syncKelurahanOptions = () => {
            const kecamatanId = kecamatanSelect.value;
            const previous = kelurahanSelect.value;

            let items = kecamatanId === ''
                ? []
                : allKelurahans.filter((item) => item.kecamatan === kecamatanId);

            // Kelurahan yang sudah tersimpan dari server dipertahankan apa adanya
            // saat halaman dimuat, walau kecamatannya tidak cocok, supaya data lama
            // tidak terhapus diam-diam. Setelah pengguna mengganti kecamatan,
            // kelurahan yang tidak sekecamatan justru harus dikosongkan.
            const saved = allKelurahans.find((item) => item.value === previous);
            if (saved && !userChangedKecamatan && !items.includes(saved)) {
                items = items.concat(saved);
            }

            kelurahanSelect.innerHTML = '';
            if (kelurahanPlaceholder) {
                kelurahanSelect.appendChild(kelurahanPlaceholder);
            }
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                option.dataset.kecamatan = item.kecamatan;
                kelurahanSelect.appendChild(option);
            });

            const keepSelection = items.some((item) => item.value === previous);
            kelurahanSelect.value = keepSelection ? previous : '';
            // Dinonaktifkan hanya saat memang belum ada pilihan sama sekali,
            // sebab select yang disabled tidak ikut terkirim saat submit —
            // kelurahan lama yang tersimpan harus tetap ikut.
            kelurahanSelect.disabled = kecamatanId === '' && !keepSelection;
        };

        kecamatanSelect.addEventListener('change', () => {
            userChangedKecamatan = true;
            syncKelurahanOptions();
        });

        syncKelurahanOptions();
    }

    const kebutuhanList = document.getElementById('kebutuhan-list');
    const addButton = document.getElementById('add-kebutuhan');
    const template = document.getElementById('kebutuhan-template');

    if (kebutuhanList && addButton && template) {
        const templateRoot = template.content || template;
        const templateOpd = templateRoot.querySelector('.opd-id');
        const templatePenanganan = templateRoot.querySelector('.jenis-penanganan');

        // Tanpa kedua daftar acuan itu penyaringan tidak bisa dijalankan. Lebih baik
        // membiarkan select apa adanya daripada mengosongkan pilihannya.
        if (!templateOpd || !templatePenanganan) {
            return;
        }

        /*
         * Pilihan di section Kebutuhan bertingkat: OPD mengikuti Jenis Kebutuhan yang
         * dipilih, lalu Jenis Penanganan mengikuti pasangan keduanya. Relasinya dibaca
         * dari data-opds dan data-kebutuhans yang menempel di option Jenis Penanganan,
         * sebab hanya relasi itulah yang menghubungkan ketiga master data tersebut.
         *
         * Daftar <option> dibangun ulang, bukan sekadar ditandai `hidden`, karena
         * Select2 menyusun dropdown dari elemen <option> dan mengabaikan `hidden` --
         * alasan yang sama dengan kecamatan -> kelurahan di atas. Perubahan daftar
         * option otomatis memicu refresh Select2 lewat MutationObserver di layout.
         *
         * Daftar aslinya dibaca sekali dari <template>, yang selalu memuat seluruh
         * option, supaya penyaringan berulang tidak menghapus pilihan selamanya.
         */
        const readOptions = (select) => Array.from(select.querySelectorAll('option')).map((option) => ({
            value: option.value,
            label: option.textContent,
            opds: JSON.parse(option.dataset.opds || '[]'),
            kebutuhans: JSON.parse(option.dataset.kebutuhans || '[]'),
        }));

        const masterOpd = readOptions(templateOpd);
        const masterPenanganan = readOptions(templatePenanganan);

        // Mengganti isi <option> tanpa menyentuh pilihan yang masih dipakai.
        // `allowed` bernilai null berarti tanpa penyaringan, dan `keepValue` yang
        // tidak lolos saringan hanya dipertahankan kalau allowKept true.
        const applyOptions = (select, master, allowed, keepValue, allowKept) => {
            const placeholder = master.find((item) => !item.value);
            const items = master.filter((item) => item.value && (!allowed || allowed.has(Number(item.value))));

            const kept = master.find((item) => item.value && item.value === keepValue);
            if (allowKept && kept && !items.includes(kept)) {
                items.push(kept);
            }

            select.innerHTML = '';
            if (placeholder) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder.label;
                select.appendChild(option);
            }
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                if (item.opds.length) {
                    option.dataset.opds = JSON.stringify(item.opds);
                }
                if (item.kebutuhans.length) {
                    option.dataset.kebutuhans = JSON.stringify(item.kebutuhans);
                }
                select.appendChild(option);
            });

            select.value = items.some((item) => item.value === keepValue) ? keepValue : '';
        };

        const filterRow = (row) => {
            const jenisKebutuhan = row.querySelector('.jenis-kebutuhan');
            const opdSelect = row.querySelector('.opd-id');
            const jenisPenanganan = row.querySelector('.jenis-penanganan');
            if (!jenisKebutuhan || !opdSelect || !jenisPenanganan) {
                return;
            }

            const selectedKebutuhan = Number(jenisKebutuhan.value) || null;

            // Baris yang belum disentuh pengguna dibiarkan apa adanya: kombinasi
            // tersimpan yang kebetulan tidak lolos relasi tetap dipertahankan supaya
            // form yang cuma dibuka lalu disimpan tidak menghapus datanya.
            const allowKept = row.dataset.touched !== '1';
            const byKebutuhan = (item) => !selectedKebutuhan || item.kebutuhans.includes(selectedKebutuhan);

            // Kedua select di bawah selalu tampil, tapi daftar pilihannya baru terisi
            // setelah pemicunya dipilih -- sebelum itu hanya berisi placeholder, jadi
            // tidak ada isian yang bisa dipilih maupun ikut terkirim.
            //
            // OPD yang boleh muncul: gabungan OPD dari jenis penanganan yang melayani
            // jenis kebutuhan terpilih. Kalau tidak ada satu pun relasi yang cocok,
            // penyaringan dilewati supaya daftarnya tidak jadi buntu.
            let allowedOpds = new Set();
            if (selectedKebutuhan) {
                masterPenanganan.forEach((item) => {
                    if (byKebutuhan(item)) {
                        item.opds.forEach((id) => allowedOpds.add(id));
                    }
                });
                if (allowedOpds.size === 0) {
                    allowedOpds = null;
                }
            }
            applyOptions(opdSelect, masterOpd, allowedOpds, opdSelect.value, allowKept);

            // Jenis Penanganan mengikuti pasangan jenis kebutuhan + OPD yang terpilih,
            // jadi isinya pun baru terisi setelah OPD dipilih.
            const selectedOpd = Number(opdSelect.value) || null;
            let allowedPenanganan = new Set();
            if (selectedKebutuhan && selectedOpd) {
                masterPenanganan.forEach((item) => {
                    if (!byKebutuhan(item)) {
                        return;
                    }
                    if (!item.opds.includes(selectedOpd)) {
                        return;
                    }
                    allowedPenanganan.add(Number(item.value));
                });
            }
            applyOptions(jenisPenanganan, masterPenanganan, allowedPenanganan, jenisPenanganan.value, allowKept);
        };

        const syncRowIndex = () => {
            const rows = Array.from(kebutuhanList.querySelectorAll('.kebutuhan-row'));
            rows.forEach((row, index) => {
                row.dataset.rowIndex = String(index);
                const heading = row.querySelector('h3');
                if (heading) {
                    heading.textContent = `Kebutuhan #${index + 1}`;
                }
                row.querySelectorAll('select, textarea').forEach((field) => {
                    field.name = field.name.replace(/kebutuhans\[\d+\]/, `kebutuhans[${index}]`);
                });
            });
        };

        kebutuhanList.addEventListener('change', (event) => {
            const target = event.target;
            const row = target.closest('.kebutuhan-row');
            if (row && (target.matches('.jenis-kebutuhan') || target.matches('.opd-id'))) {
                row.dataset.touched = '1';
                filterRow(row);
            }
        });

        kebutuhanList.addEventListener('click', (event) => {
            const target = event.target.closest('.remove-kebutuhan');
            if (!target) {
                return;
            }
            const rows = kebutuhanList.querySelectorAll('.kebutuhan-row');
            if (rows.length <= 1) {
                return;
            }
            target.closest('.kebutuhan-row')?.remove();
            syncRowIndex();
        });

        addButton.addEventListener('click', () => {
            const index = kebutuhanList.querySelectorAll('.kebutuhan-row').length;
            const html = template.innerHTML.replaceAll('__INDEX__', String(index)).replaceAll('__NUMBER__', String(index + 1));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const newRow = wrapper.firstElementChild;
            if (!newRow) {
                return;
            }
            kebutuhanList.appendChild(newRow);
            syncRowIndex();
            // Baris baru mulai dari kolom yang paling awal saja: OPD dan Jenis
            // Penanganan masih kosong sampai pemicunya dipilih.
            filterRow(newRow);
        });

        kebutuhanList.querySelectorAll('.kebutuhan-row').forEach((row) => filterRow(row));
    }
})();
</script>
@endpush

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

    // Titik tengah awal: Kota Semarang, dipakai kalau pasien belum punya koordinat.
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
    // pasien tidak menanggung beratnya kalau petanya memang tidak dipakai.
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

@push('scripts')
<script>
(function () {
    /*
     * Dua penjaga isian di card Data Pasien: nama selalu huruf besar, NIK selalu angka
     * maksimal 16 digit.
     *
     * Keduanya hanya menyaring apa yang sedang diketik, bukan mengubah nilai yang sudah
     * tersimpan begitu halaman dibuka -- form edit yang cuma dibuka lalu disimpan tidak
     * boleh mengubah datanya sendiri, jadi nilai lama dibiarkan apa adanya sampai
     * penggunanya menyentuh kolom itu.
     *
     * Penyaringan ini murni bantuan sisi peramban; aturan yang mengikat tetap ada di
     * validasi server (name max:255, nik required + digits:16), sehingga kiriman yang
     * tidak lewat form ini tetap ditolak. Huruf pada NIK ikut disaring di sini supaya
     * kesalahannya ketahuan saat mengetik, bukan setelah form disimpan.
     *
     * Posisi kursor selalu dikembalikan setelah nilai kolom diganti, sebab menulis ke
     * .value memindahkan kursor ke ujung kanan.
     */

    const forceUppercase = (input) => {
        input.addEventListener('input', () => {
            const upper = input.value.toUpperCase();
            if (upper === input.value) {
                return;
            }

            const caret = Math.min(input.selectionStart, upper.length);
            input.value = upper;
            input.setSelectionRange(caret, caret);
        });
    };

    const digitsOnly = (input, maxLength) => {
        input.addEventListener('input', () => {
            // Jumlah digit di sebelah kiri kursor dihitung lebih dulu, supaya kursor
            // tetap menempel di posisi semula setelah karakter non-angka dibuang.
            const caretDigits = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;
            const digits = input.value.replace(/\D/g, '').slice(0, maxLength);
            if (digits === input.value) {
                return;
            }

            input.value = digits;
            const caret = Math.min(caretDigits, digits.length);
            input.setSelectionRange(caret, caret);
        });
    };

    const nameInput = document.getElementById('name');
    if (nameInput) {
        forceUppercase(nameInput);
    }

    const nikInput = document.getElementById('nik');
    if (nikInput) {
        digitsOnly(nikInput, 16);
    }
})();
</script>
@endpush
