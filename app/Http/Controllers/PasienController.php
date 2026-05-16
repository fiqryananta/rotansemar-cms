<?php

namespace App\Http\Controllers;

use App\Exports\PasienImportTemplateExport;
use App\Imports\PasienImport;
use App\Models\Faskes;
use App\Models\JenisKebutuhan;
use App\Models\JenisPenanganan;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Opd;
use App\Models\Pasien;
use App\Models\Pekerjaan;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $pasiens = $this->applyPasienScope(Pasien::query(), $request->user())
            ->with([
                'faskes:id,name',
                'kelurahan:id,name',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%')
                        ->orWhereHas('faskes', function ($faskesQuery) use ($search) {
                            $faskesQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('kelurahan', function ($kelurahanQuery) use ($search) {
                            $kelurahanQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $payload = [
            'pasiens' => $pasiens,
            'links' => $pasiens->linkCollection(),
            'filters' => [
                'search' => $search ?? '',
                'per_page' => $perPage,
            ],
        ];

        return view('pasiens.index', $payload);
    }

    public function create()
    {
        $payload = $this->pasienFormOptions();

        return view('pasiens.create', $payload);
    }

    public function importPage(Request $request)
    {
        abort_unless($this->isAdminUser($request->user()), 403);

        $payload = [
            'importSummary' => session('import_summary'),
            'importErrors' => session('import_errors', []),
            'templateColumns' => [
                'name',
                'nik',
                'birth_date',
                'gender',
                'faskes_id',
                'puskesmas_id',
                'kecamatan_id',
                'kelurahan_id',
                'address',
                'coordinates',
                'treatment_start_date',
                'catatan_kebutuhan',
            ],
        ];

        return view('pasiens.import', $payload);
    }

    public function importTemplate(Request $request)
    {
        abort_unless($this->isAdminUser($request->user()), 403);

        return Excel::download(new PasienImportTemplateExport(), 'template-import-pasien.xlsx');
    }

    public function importStore(Request $request)
    {
        abort_unless($this->isAdminUser($request->user()), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new PasienImport();
        Excel::import($import, $request->file('file'));

        return redirect()
            ->route('pasiens.import.index')
            ->with('import_summary', $import->summary())
            ->with('import_errors', $import->errors());
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $validated = $this->enforcePasienScopePayload($validated, $request);

        $kebutuhans = $validated['kebutuhans'];
        unset($validated['kebutuhans']);

        // Derive jenis_kebutuhan_ids from the kebutuhan rows
        $jenisKebutuhanIds = array_values(array_unique(array_column($kebutuhans, 'jenis_kebutuhan_id')));

        $pasien = Pasien::create($validated);
        $pasien->jenisKebutuhans()->sync($jenisKebutuhanIds);
        $this->syncKebutuhans($pasien, $kebutuhans);

        return redirect()->route('pasiens.index')->with('success', 'Pasien created successfully');
    }

    public function edit(Request $request, Pasien $pasien)
    {
        abort_unless($this->canAccessPasien($request->user(), $pasien), 403);

        $pasien->load([
            'jenisKebutuhans:id,name',
            'kebutuhans:id,pasien_id,jenis_kebutuhan_id,opd_id,jenis_penanganan_id,need_detail,verification_status',
            'kebutuhans.jenisKebutuhan:id,name',
            'kebutuhans.opd:id,name',
            'kebutuhans.jenisPenanganan:id,name',
        ]);

        $payload = array_merge(['pasien' => $pasien], $this->pasienFormOptions());

        return view('pasiens.edit', $payload);
    }

    public function show(Request $request, Pasien $pasien)
    {
        abort_unless($this->canAccessPasien($request->user(), $pasien), 403);

        $pasien->load([
            'faskes:id,name',
            'puskesmas:id,name',
            'kecamatan:id,name',
            'kelurahan:id,name',
            'pekerjaan:id,name',
            'familyHeadPekerjaan:id,name',
            'jenisKebutuhans:id,name',
            'kebutuhans:id,pasien_id,jenis_kebutuhan_id,opd_id,jenis_penanganan_id,need_detail,verification_status',
            'kebutuhans.jenisKebutuhan:id,name',
            'kebutuhans.opd:id,name',
            'kebutuhans.jenisPenanganan:id,name',
        ]);

        $payload = [
            'pasien' => $pasien,
            'renderValue' => fn ($value) => blank($value) ? '-' : $value,
            'renderBoolean' => fn ($value) => is_null($value) ? '-' : ($value ? 'Ya' : 'Tidak'),
        ];

        return view('pasiens.show', $payload);
    }

    public function update(Request $request, Pasien $pasien)
    {
        abort_unless($this->canAccessPasien($request->user(), $pasien), 403);

        $validated = $this->validatePayload($request, $pasien);
        $validated = $this->enforcePasienScopePayload($validated, $request);

        $kebutuhans = $validated['kebutuhans'];
        unset($validated['kebutuhans']);

        // Derive jenis_kebutuhan_ids from the kebutuhan rows
        $jenisKebutuhanIds = array_values(array_unique(array_column($kebutuhans, 'jenis_kebutuhan_id')));

        $pasien->update($validated);
        $pasien->jenisKebutuhans()->sync($jenisKebutuhanIds);
        $this->syncKebutuhans($pasien, $kebutuhans);

        return redirect()->route('pasiens.index')->with('success', 'Pasien updated successfully');
    }

    public function destroy(Request $request, Pasien $pasien)
    {
        abort_unless($this->canAccessPasien($request->user(), $pasien), 403);

        $pasien->delete();

        return redirect()->route('pasiens.index')->with('success', 'Pasien deleted successfully');
    }

    private function validatePayload(Request $request, ?Pasien $pasien = null): array
    {
        $payload = $this->normalizePayload($request->all());

        return Validator::make($payload, [
            // Data Diri
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'digits:16',
                Rule::unique('pasiens', 'nik')->ignore($pasien?->id),
            ],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'faskes_id' => ['required', 'exists:faskes,id'],
            'puskesmas_id' => ['required', 'exists:puskesmas,id'],
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'kelurahan_id' => ['required', 'exists:kelurahans,id'],
            'address' => ['required', 'string'],
            'coordinates' => ['nullable', 'string', 'max:255'],
            'new_address' => ['nullable', 'string'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'ever_received_assistance' => ['required', 'boolean'],
            'willing_to_help' => ['required', 'boolean'],

            // Pekerjaan
            'economic_status' => ['nullable', Rule::in(['miskin', 'sederhana', 'mampu'])],
            'pekerjaan_id' => ['nullable', 'exists:pekerjaan,id'],
            'workplace_name' => ['nullable', 'string', 'max:255'],
            'workplace_address' => ['nullable', 'string', 'max:255'],

            // Keluarga
            'family_head_name' => ['nullable', 'string', 'max:255'],
            'family_head_nik' => ['nullable', 'digits:16'],
            'family_head_pekerjaan_id' => ['nullable', 'exists:pekerjaan,id'],
            'parent_marital_status' => ['nullable', Rule::in(['menikah', 'cerai'])],
            'parenting_pattern' => ['nullable', 'string', 'max:255'],
            'family_income_range' => ['nullable', Rule::in(['<1000000', '>1000000'])],
            'respondent' => ['required', 'boolean'],
            'patient_relationship' => ['nullable', Rule::in(['pasien-sendiri', 'orang-tua', 'anak'])],

            // Riwayat Kesehatan
            'tb_so_ro' => ['nullable', 'integer', 'min:0'],
            'treatment_start_date' => ['nullable', 'date'],
            'visit_date' => ['nullable', 'date'],
            'information_date' => ['nullable', 'date'],
            'treatment_status' => ['nullable', Rule::in(['terlaksana', 'belum'])],
            'transmission_source' => ['nullable', 'string', 'max:255'],
            'follow_up_plan' => ['nullable', 'string'],
            'pregnancy_status' => ['required', 'boolean'],
            'comorbid_status' => ['required', 'boolean'],
            'smoking_behavior' => ['required', 'boolean'],
            'family_smoking_status' => ['required', 'boolean'],
            'immunization_status' => ['nullable', Rule::in(['lengkap', 'tidak-lengkap'])],
            'nutritional_status' => ['nullable', Rule::in(['normal', 'tidak-normal'])],
            'jkn_ownership' => ['required', 'boolean'],

            // Kondisi Rumah
            'home_area' => ['nullable', 'string', 'max:255'],
            'house_area' => ['nullable', 'string', 'max:255'],
            'house_type' => ['nullable', 'string', 'max:255'],
            'house_status' => ['nullable', 'string', 'max:255'],
            'home_lighting' => ['nullable', 'string', 'max:255'],
            'home_humidity' => ['nullable', 'string', 'max:255'],
            'home_cleanliness' => ['nullable', 'string', 'max:255'],
            'home_floor' => ['nullable', 'string', 'max:255'],
            'home_ventilation' => ['nullable', 'string', 'max:255'],
            'home_ceiling' => ['nullable', 'string', 'max:255'],
            'home_ceiling_condition' => ['nullable', 'string', 'max:255'],
            'home_wall' => ['nullable', 'string', 'max:255'],
            'home_bedroom_window' => ['nullable', 'string', 'max:255'],
            'home_family_room_window' => ['nullable', 'string', 'max:255'],
            'home_kitchen_smoke_hole' => ['nullable', 'string', 'max:255'],
            'home_open_family_room_window' => ['nullable', 'string', 'max:255'],
            'home_clean_house_habit' => ['nullable', 'string', 'max:255'],

            // Kondisi Sanitasi
            'sanitation_clean_water' => ['nullable', 'string', 'max:255'],
            'sanitation_toilet' => ['nullable', 'string', 'max:255'],
            'sanitation_wastewater_disposal' => ['nullable', 'string', 'max:255'],
            'sanitation_garbage_water_disposal' => ['nullable', 'string', 'max:255'],
            'sanitation_trash' => ['nullable', 'string', 'max:255'],
            'sanitation_feces_disposal' => ['nullable', 'string', 'max:255'],
            'sanitation_throw_trash_habit' => ['nullable', 'string', 'max:255'],
            'sanitation_handwashing_habit' => ['nullable', 'string', 'max:255'],

            // Kondisi Hewan
            'has_livestock' => ['nullable', 'boolean'],
            'has_animal_cage' => ['nullable', 'boolean'],

            // Catatan & Kebutuhan
            'catatan_kebutuhan' => ['nullable', 'string'],
            'kebutuhans' => ['required', 'array', 'min:1'],
            'kebutuhans.*.jenis_kebutuhan_id' => ['required', 'exists:jenis_kebutuhans,id'],
            'kebutuhans.*.opd_id' => ['required', 'exists:opds,id'],
            'kebutuhans.*.jenis_penanganan_id' => ['required', 'exists:jenis_penanganans,id'],
            'kebutuhans.*.need_detail' => ['required', 'string'],
        ])->after(function ($validator) use ($payload) {
            $kebutuhansPayload = $payload['kebutuhans'] ?? [];
            $jkIds = array_values(array_unique(array_filter(array_column($kebutuhansPayload, 'jenis_kebutuhan_id'))));

            $allowedMap = JenisKebutuhan::query()
                ->with('jenisPenanganans:id')
                ->whereIn('id', $jkIds)
                ->get()
                ->mapWithKeys(fn($jk) => [$jk->id => $jk->jenisPenanganans->pluck('id')->all()])
                ->toArray();

            foreach ($kebutuhansPayload as $index => $item) {
                $jkId = (int) ($item['jenis_kebutuhan_id'] ?? 0);
                $jpId = (int) ($item['jenis_penanganan_id'] ?? 0);

                if (!$jkId || !$jpId) {
                    continue;
                }

                $allowed = $allowedMap[$jkId] ?? [];
                if (!in_array($jpId, $allowed, true)) {
                    $validator->errors()->add("kebutuhans.$index.jenis_penanganan_id", 'Jenis penanganan tidak sesuai dengan jenis kebutuhan.');
                }
            }
        })->validate();
    }

    private function normalizePayload(array $payload): array
    {
        $nullableFields = [
            'coordinates',
            'new_address',
            'weight',
            'height',
            'economic_status',
            'pekerjaan_id',
            'workplace_name',
            'workplace_address',
            'family_head_name',
            'family_head_nik',
            'family_head_pekerjaan_id',
            'parent_marital_status',
            'parenting_pattern',
            'family_income_range',
            'patient_relationship',
            'tb_so_ro',
            'treatment_start_date',
            'visit_date',
            'information_date',
            'treatment_status',
            'transmission_source',
            'follow_up_plan',
            'immunization_status',
            'nutritional_status',
            'home_area',
            'house_area',
            'house_type',
            'house_status',
            'home_lighting',
            'home_humidity',
            'home_cleanliness',
            'home_floor',
            'home_ventilation',
            'home_ceiling',
            'home_ceiling_condition',
            'home_wall',
            'home_bedroom_window',
            'home_family_room_window',
            'home_kitchen_smoke_hole',
            'home_open_family_room_window',
            'home_clean_house_habit',
            'sanitation_clean_water',
            'sanitation_toilet',
            'sanitation_wastewater_disposal',
            'sanitation_garbage_water_disposal',
            'sanitation_trash',
            'sanitation_feces_disposal',
            'sanitation_throw_trash_habit',
            'sanitation_handwashing_habit',
            'has_livestock',
            'has_animal_cage',
            'catatan_kebutuhan',
        ];

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] === '') {
                $payload[$field] = null;
            }
        }

        return $payload;
    }

    private function syncKebutuhans(Pasien $pasien, array $kebutuhans): void
    {
        $pasien->kebutuhans()->delete();

        $rows = array_map(function ($item) {
            return [
                'jenis_kebutuhan_id' => $item['jenis_kebutuhan_id'],
                'opd_id' => $item['opd_id'],
                'jenis_penanganan_id' => $item['jenis_penanganan_id'],
                'need_detail' => $item['need_detail'],
                'verification_status' => 'pending',
            ];
        }, $kebutuhans);

        $pasien->kebutuhans()->createMany($rows);
    }

    private function applyPasienScope($query, $user)
    {
        if (!$user) {
            return $query;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return $query;
        }

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            return $query->where('puskesmas_id', $user->puskesmas_id);
        }

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            return $query->where('kecamatan_id', $user->kecamatan_id);
        }

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            return $query->where('kelurahan_id', $user->kelurahan_id);
        }

        if ($roles->contains('opd') && $user->opd_id) {
            return $query->whereHas('kebutuhans', fn ($kq) => $kq->where('opd_id', $user->opd_id));
        }

        return $query->whereRaw('1 = 0');
    }

    private function canAccessPasien($user, Pasien $pasien): bool
    {
        if (!$user) {
            return false;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('admin')) {
            return true;
        }

        if ($roles->contains('puskesmas')) {
            return (int) $user->puskesmas_id === (int) $pasien->puskesmas_id;
        }

        if ($roles->contains('kecamatan')) {
            return (int) $user->kecamatan_id === (int) $pasien->kecamatan_id;
        }

        if ($roles->contains('kelurahan')) {
            return (int) $user->kelurahan_id === (int) $pasien->kelurahan_id;
        }

        if ($roles->contains('opd') && $user->opd_id) {
            return $pasien->kebutuhans()->where('opd_id', $user->opd_id)->exists();
        }

        return false;
    }

    private function enforcePasienScopePayload(array $validated, Request $request): array
    {
        $user = $request->user();
        if (!$user) {
            return $validated;
        }

        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));

        if ($roles->contains('puskesmas') && $user->puskesmas_id) {
            $validated['puskesmas_id'] = (int) $user->puskesmas_id;
        }

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            $validated['kecamatan_id'] = (int) $user->kecamatan_id;
        }

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            $validated['kelurahan_id'] = (int) $user->kelurahan_id;
        }

        return $validated;
    }

    private function pasienFormOptions(): array
    {
        return [
            'faskes' => Faskes::query()->orderBy('name')->get(['id', 'name']),
            'puskesmas' => Puskesmas::query()->orderBy('name')->get(['id', 'name']),
            'kecamatans' => Kecamatan::query()->orderBy('name')->get(['id', 'name']),
            'kelurahans' => Kelurahan::query()->orderBy('name')->get(['id', 'name', 'kecamatan_id']),
            'pekerjaan' => Pekerjaan::query()->orderBy('name')->get(['id', 'name']),
            'opds' => Opd::query()->orderBy('name')->get(['id', 'name']),
            'jenisPenanganans' => JenisPenanganan::query()->with(['opds:id,name', 'jenisKebutuhans:id,name'])->orderBy('name')->get(['id', 'name']),
            'jenisKebutuhans' => JenisKebutuhan::query()->with('jenisPenanganans:id,name')->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function isAdminUser($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn ($name) => strtolower((string) $name))
            ->contains('admin');
    }
}


