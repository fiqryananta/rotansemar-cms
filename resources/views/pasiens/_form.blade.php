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
        '' => '-',
        '1' => 'Ya',
        '0' => 'Tidak',
    ];

    $selectNullableYesNo = [
        '' => '-',
        '1' => 'Ya',
        '0' => 'Tidak',
    ];
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
            <button type="button" data-tab-target="1" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-cyan-300 hover:text-cyan-700">Riwayat Kesehatan</button>
            <button type="button" data-tab-target="2" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-cyan-300 hover:text-cyan-700">Data Lainnya</button>
            <button type="button" data-tab-target="3" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-cyan-300 hover:text-cyan-700">Kebutuhan</button>
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
                    <input id="name" name="name" value="{{ $value('name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="nik">NIK</label>
                    <input id="nik" name="nik" value="{{ $value('nik') }}" maxlength="16" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('nik')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="birth_date">Tanggal Lahir</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ $value('birth_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('birth_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih</option>
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
                    <label class="block text-sm font-medium text-gray-700" for="coordinates">Koordinat</label>
                    <input id="coordinates" name="coordinates" value="{{ $value('coordinates') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('coordinates')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="weight">Berat Badan</label>
                    <input id="weight" name="weight" type="number" min="0" value="{{ $value('weight') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('weight')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="height">Tinggi Badan</label>
                    <input id="height" name="height" type="number" min="0" value="{{ $value('height') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('height')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="address">Alamat</label>
                    <textarea id="address" name="address" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ $value('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="new_address">Alamat Baru</label>
                    <textarea id="new_address" name="new_address" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ $value('new_address') }}</textarea>
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
                        <option value="">Pilih</option>
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
                    <input id="workplace_name" name="workplace_name" value="{{ $value('workplace_name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="workplace_address">Alamat Tempat Bekerja</label>
                    <input id="workplace_address" name="workplace_address" value="{{ $value('workplace_address') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_head_name">Nama Kepala Keluarga</label>
                    <input id="family_head_name" name="family_head_name" value="{{ $value('family_head_name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_head_nik">NIK Kepala Keluarga</label>
                    <input id="family_head_nik" name="family_head_nik" maxlength="16" value="{{ $value('family_head_nik') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
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
                        <option value="">Pilih</option>
                        <option value="menikah" @selected($value('parent_marital_status') === 'menikah')>Menikah</option>
                        <option value="cerai" @selected($value('parent_marital_status') === 'cerai')>Cerai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="parenting_pattern">Pola Asuh</label>
                    <input id="parenting_pattern" name="parenting_pattern" value="{{ $value('parenting_pattern') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="family_income_range">Rentang Pendapatan</label>
                    <select id="family_income_range" name="family_income_range" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih</option>
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
                        <option value="">Pilih</option>
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
                <div><label class="block text-sm font-medium text-gray-700" for="tb_so_ro">TB SO/RO</label><input id="tb_so_ro" name="tb_so_ro" type="number" min="0" value="{{ $value('tb_so_ro') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" /></div>
                <div><label class="block text-sm font-medium text-gray-700" for="treatment_start_date">Tanggal Mulai Pengobatan</label><input id="treatment_start_date" name="treatment_start_date" type="date" value="{{ $value('treatment_start_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" /></div>
                <div><label class="block text-sm font-medium text-gray-700" for="visit_date">Tanggal Kunjungan</label><input id="visit_date" name="visit_date" type="date" value="{{ $value('visit_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" /></div>
                <div><label class="block text-sm font-medium text-gray-700" for="information_date">Tanggal Informasi</label><input id="information_date" name="information_date" type="date" value="{{ $value('information_date') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" /></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="treatment_status">Status Pengobatan</label>
                    <select id="treatment_status" name="treatment_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih</option>
                        <option value="terlaksana" @selected($value('treatment_status') === 'terlaksana')>Terlaksana</option>
                        <option value="belum" @selected($value('treatment_status') === 'belum')>Belum</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700" for="transmission_source">Sumber Penularan</label><input id="transmission_source" name="transmission_source" value="{{ $value('transmission_source') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" /></div>
                <div class="md:col-span-2 lg:col-span-3"><label class="block text-sm font-medium text-gray-700" for="follow_up_plan">Rencana Tindak Lanjut</label><textarea id="follow_up_plan" name="follow_up_plan" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ $value('follow_up_plan') }}</textarea></div>
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
                        <option value="">Pilih</option>
                        <option value="lengkap" @selected($value('immunization_status') === 'lengkap')>Lengkap</option>
                        <option value="tidak-lengkap" @selected($value('immunization_status') === 'tidak-lengkap')>Tidak Lengkap</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="nutritional_status">Status Gizi</label>
                    <select id="nutritional_status" name="nutritional_status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih</option>
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
                        <input id="{{ $field }}" name="{{ $field }}" value="{{ $value($field) }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
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
                        <input id="{{ $field }}" name="{{ $field }}" value="{{ $value($field) }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
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
                    <textarea id="catatan_kebutuhan" name="catatan_kebutuhan" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ $value('catatan_kebutuhan') }}</textarea>
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
                                        <option value="">Pilih</option>
                                        @foreach ($jenisKebutuhans as $item)
                                            <option value="{{ $item->id }}" @selected((string) data_get($row, 'jenis_kebutuhan_id', '') === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("kebutuhans.$index.jenis_kebutuhan_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">OPD</label>
                                    <select name="kebutuhans[{{ $index }}][opd_id]" class="opd-id mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih</option>
                                        @foreach ($opds as $item)
                                            <option value="{{ $item->id }}" @selected((string) data_get($row, 'opd_id', '') === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("kebutuhans.$index.opd_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jenis Penanganan</label>
                                    <select name="kebutuhans[{{ $index }}][jenis_penanganan_id]" class="jenis-penanganan mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih</option>
                                        @foreach ($jenisPenanganans as $item)
                                            <option value="{{ $item->id }}" data-opds='@json($item->opds->pluck("id"))' data-kebutuhans='@json($item->jenisKebutuhans->pluck("id"))' @selected((string) data_get($row, 'jenis_penanganan_id', '') === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("kebutuhans.$index.jenis_penanganan_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Detail Kebutuhan</label>
                                    <textarea name="kebutuhans[{{ $index }}][need_detail]" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ data_get($row, 'need_detail', '') }}</textarea>
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
                    <option value="">Pilih</option>
                    @foreach ($jenisKebutuhans as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">OPD</label>
                <select name="kebutuhans[__INDEX__][opd_id]" class="opd-id mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih</option>
                    @foreach ($opds as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Penanganan</label>
                <select name="kebutuhans[__INDEX__][jenis_penanganan_id]" class="jenis-penanganan mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih</option>
                    @foreach ($jenisPenanganans as $item)
                        <option value="{{ $item->id }}" data-opds='@json($item->opds->pluck("id"))' data-kebutuhans='@json($item->jenisKebutuhans->pluck("id"))'>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Detail Kebutuhan</label>
                <textarea name="kebutuhans[__INDEX__][need_detail]" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
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

    const kelurahanSelect = document.getElementById('kelurahan_id');
    const kecamatanSelect = document.getElementById('kecamatan_id');
    if (kelurahanSelect && kecamatanSelect) {
        const filterKelurahan = () => {
            const kecamatanId = kecamatanSelect.value;
            Array.from(kelurahanSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }
                option.hidden = Boolean(kecamatanId) && option.dataset.kecamatan !== kecamatanId;
            });
            if (kelurahanSelect.selectedOptions[0] && kelurahanSelect.selectedOptions[0].hidden) {
                kelurahanSelect.value = '';
            }
        };
        kecamatanSelect.addEventListener('change', filterKelurahan);
        filterKelurahan();
    }

    const kebutuhanList = document.getElementById('kebutuhan-list');
    const addButton = document.getElementById('add-kebutuhan');
    const template = document.getElementById('kebutuhan-template');

    if (kebutuhanList && addButton && template) {
        const filterRow = (row) => {
            const jenisKebutuhan = row.querySelector('.jenis-kebutuhan');
            const opdSelect = row.querySelector('.opd-id');
            const jenisPenanganan = row.querySelector('.jenis-penanganan');
            if (!jenisKebutuhan || !opdSelect || !jenisPenanganan) {
                return;
            }

            const selectedKebutuhan = Number(jenisKebutuhan.value) || null;
            const selectedOpd = Number(opdSelect.value) || null;
            const selectedPenanganan = jenisPenanganan.value;
            const selectedOpdValue = opdSelect.value;

            // --- Step 1: filter jenisPenanganan by kebutuhan + opd ---
            const penangananOptions = Array.from(jenisPenanganan.querySelectorAll('option'));
            penangananOptions.forEach((opt) => {
                if (!opt.value) { opt.hidden = false; return; }
                const allowedKebutuhans = JSON.parse(opt.dataset.kebutuhans || '[]');
                const allowedOpds = JSON.parse(opt.dataset.opds || '[]');
                const byKebutuhan = !selectedKebutuhan || allowedKebutuhans.includes(selectedKebutuhan);
                const byOpd = !selectedOpd || allowedOpds.includes(selectedOpd);
                opt.hidden = !(byKebutuhan && byOpd);
            });

            // reset penanganan if now hidden
            const currentPenanganan = jenisPenanganan.querySelector(`option[value="${selectedPenanganan}"]`);
            if (currentPenanganan && currentPenanganan.hidden) {
                jenisPenanganan.value = '';
            }

            // --- Step 2: filter OPD based on kebutuhan only (ignoring current opd selection) ---
            // collect OPD IDs that appear in any penanganan allowed by selected kebutuhan
            const allowedOpdIds = new Set();
            penangananOptions.forEach((opt) => {
                if (!opt.value) return;
                const allowedKebutuhans = JSON.parse(opt.dataset.kebutuhans || '[]');
                if (!selectedKebutuhan || allowedKebutuhans.includes(selectedKebutuhan)) {
                    JSON.parse(opt.dataset.opds || '[]').forEach((id) => allowedOpdIds.add(id));
                }
            });

            const opdOptions = Array.from(opdSelect.querySelectorAll('option'));
            opdOptions.forEach((opt) => {
                if (!opt.value) { opt.hidden = false; return; }
                opt.hidden = allowedOpdIds.size > 0 && !allowedOpdIds.has(Number(opt.value));
            });

            // reset opd if now hidden
            const currentOpd = opdSelect.querySelector(`option[value="${selectedOpdValue}"]`);
            if (currentOpd && currentOpd.hidden) {
                opdSelect.value = '';
                // re-run penanganan filter with empty opd
                penangananOptions.forEach((opt) => {
                    if (!opt.value) { opt.hidden = false; return; }
                    const allowedKebutuhans = JSON.parse(opt.dataset.kebutuhans || '[]');
                    const byKebutuhan = !selectedKebutuhan || allowedKebutuhans.includes(selectedKebutuhan);
                    opt.hidden = !byKebutuhan;
                });
                const stillSelected = jenisPenanganan.querySelector(`option[value="${jenisPenanganan.value}"]`);
                if (stillSelected && stillSelected.hidden) {
                    jenisPenanganan.value = '';
                }
            }
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
        });

        kebutuhanList.querySelectorAll('.kebutuhan-row').forEach((row) => filterRow(row));
    }
})();
</script>
@endpush
