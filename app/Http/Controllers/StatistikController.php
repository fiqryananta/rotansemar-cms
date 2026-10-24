<?php

namespace App\Http\Controllers;

use App\Models\JenisKebutuhan;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Opd;
use App\Models\PasienKebutuhan;
use App\Models\User;
use App\Support\MobileAccessScope;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StatistikController extends Controller
{
    /**
     * Panjang rentang grafik bulanan: bulan berjalan mundur 12 bulan.
     */
    private const MONTHS = 12;

    /**
     * Singkatan bulan bahasa Indonesia. Tidak memakai terjemahan Carbon karena
     * locale aplikasi masih 'en' (lihat config/app.php).
     */
    private const MONTH_ABBR = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function index(Request $request)
    {
        $baseQuery = MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user());

        return view('statistik.index', [
            'statusLabels'     => PasienKebutuhan::STATUS_LABELS,
            'opdChart'         => $this->opdChart($baseQuery, $request->user()),
            'statusMonthly'    => $this->statusMonthlyChart($baseQuery),
            'kebutuhanMonthly' => $this->kebutuhanMonthlyChart($baseQuery),
            'kebutuhanPie'     => $this->kebutuhanPieChart($baseQuery),
        ]);
    }

    /**
     * Rekap tabel per OPD: jumlah pasien dan sebarannya per status verifikasi.
     *
     * Barisnya mengikuti daftar OPD di master data (lihat opdNames()), bukan cuma
     * OPD yang sudah punya baris kebutuhan, jadi OPD yang datanya masih kosong tetap
     * muncul dengan nilai 0 dan tabelnya tidak menyusut jadi kosong.
     */
    public function rekapOpd(Request $request)
    {
        $baseQuery = MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user());

        $rows = (clone $baseQuery)
            ->selectRaw('opd_id as wilayah_id, ' . $this->rekapColumns())
            ->whereNotNull('opd_id')
            ->groupBy('opd_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $this->opdNames($request->user(), $rows);

        return view('statistik.rekap-opd', [
            'statusLabels' => PasienKebutuhan::STATUS_LABELS,
            'opdSummary' => $names
                ->map(fn ($name, $id) => $this->rekapRow($rows->get($id), $name, (int) $id))
                ->values(),
            'totals'     => $this->rekapTotals((clone $baseQuery)->whereNotNull('opd_id')),
        ]);
    }

    /**
     * Rincian pasien untuk satu OPD, dipanggil lewat AJAX dari modal Rekap OPD.
     *
     * Tanpa parameter status, yang dikirim adalah seluruh pasien yang punya
     * kebutuhan di OPD itu. Dengan ?status=selesai (misalnya), daftarnya dibatasi
     * pada pasien yang punya kebutuhan berstatus itu, dan baris kebutuhan yang
     * ikut dikirim juga hanya yang berstatus itu. Dengan begitu isi modal selalu
     * sama dengan angka yang barusan diklik di tabel rekap.
     *
     * Scope aksesnya memakai scopeDashboard yang sama dengan tabelnya, jadi user
     * yang ter-scope ke satu OPD/faskes/puskesmas/wilayah tidak bisa membuka data
     * di luar jangkauannya walau id OPD di URL diganti.
     */
    public function rekapOpdDetail(Request $request, Opd $opd)
    {
        [$status, $jenis, $filterLabel] = $this->detailFilters($request);

        $kebutuhans = $this->applyDetailFilters(
            MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user())
                ->where('pasien_kebutuhans.opd_id', $opd->id),
            $status,
            $jenis
        )
            ->with([
                'pasien:id,name,nik',
                'jenisKebutuhan:id,name',
                'opd:id,name',
                'jenisPenanganan:id,name',
            ])
            ->withCount('tindakLanjuts')
            ->orderBy('pasien_kebutuhans.id')
            ->get();

        return $this->detailResponse($opd->name, $filterLabel, $kebutuhans);
    }

    /**
     * Rekap tabel per kecamatan. Kolomnya identik dengan Rekap OPD, hanya
     * pengelompokannya yang berbeda.
     *
     * Barisnya juga mengikuti daftar kecamatan di master data (lihat
     * kecamatanNames()), jadi kecamatan yang belum ada datanya tetap muncul dengan
     * nilai 0, bukan hilang dari tabel.
     */
    public function rekapWilayah(Request $request)
    {
        $baseQuery = MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user());
        [$kecamatanId, $kelurahanId] = $this->wilayahFilterIds($request);

        $kecamatans = $this->wilayahFilterOptions($request->user());

        // Baris tabel: agregat dengan filter yang sedang aktif. Kalau satu
        // kecamatan dipilih, barisnya tetap ditampilkan walau datanya kosong,
        // supaya pilihan filternya tidak berujung di tabel kosong.
        $rows = $this->applyWilayahFilter($this->wilayahQuery($baseQuery), $kecamatanId, $kelurahanId)
            ->selectRaw('pasiens.kecamatan_id as wilayah_id, ' . $this->rekapColumns())
            ->groupBy('pasiens.kecamatan_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $kecamatanId > 0
            ? $kecamatans->only([$kecamatanId])
            : $this->kecamatanNames($request->user(), $rows);

        return view('statistik.rekap-wilayah', [
            'statusLabels'     => PasienKebutuhan::STATUS_LABELS,
            'kecamatanSummary' => $names
                ->map(fn ($name, $id) => $this->rekapRow($rows->get($id), $name, (int) $id))
                ->values(),
            'totals'           => $this->rekapTotals(
                $this->applyWilayahFilter($this->wilayahQuery($baseQuery), $kecamatanId, $kelurahanId)
            ),
            'filters'          => ['kecamatan_id' => $kecamatanId, 'kelurahan_id' => $kelurahanId],
            'kecamatans'       => $kecamatans,
            'kelurahans'       => $this->kelurahanOptions($request->user(), $kecamatans),
        ]);
    }

    /**
     * Rincian per kelurahan untuk satu kecamatan, dipanggil lewat AJAX saat baris
     * kecamatan diklik.
     *
     * Barisnya mengikuti daftar kelurahan di master data untuk kecamatan itu (lihat
     * kelurahanNames()), jadi kecamatan yang datanya masih kosong tetap membuka
     * daftar kelurahannya dengan nilai 0, bukan pesan "belum ada data".
     *
     * Memakai scope yang sama dengan tabelnya, jadi user yang ter-scope ke satu
     * kecamatan atau kelurahan tetap tidak bisa melihat wilayah lain walau id
     * kecamatan di URL diganti -- scope dan filter kecamatan berlaku bersamaan.
     */
    public function rekapKelurahan(Request $request, Kecamatan $kecamatan)
    {
        $baseQuery = MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user());
        $kelurahanId = (int) $request->query('kelurahan_id', 0);

        $rows = $this->wilayahQuery($baseQuery)
            ->selectRaw('pasiens.kelurahan_id as wilayah_id, ' . $this->rekapColumns())
            ->where('pasiens.kecamatan_id', $kecamatan->id)
            // Filter kelurahan di tabel ikut berlaku di sini, supaya angka baris
            // induk dan rinciannya selalu berasal dari himpunan data yang sama.
            ->when($kelurahanId > 0, fn ($query) => $query->where('pasiens.kelurahan_id', $kelurahanId))
            ->groupBy('pasiens.kelurahan_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $this->kelurahanNames($request->user(), $rows, $kecamatan);

        return response()->json([
            'kecamatan' => $kecamatan->name,
            'rows'      => $names
                ->map(fn ($name, $id) => $this->rekapRow($rows->get($id), $name, (int) $id))
                ->values()
                ->all(),
        ]);
    }

    /**
     * Rincian pasien untuk satu kecamatan, dipanggil lewat AJAX dari modal Rekap
     * Wilayah -- perilakunya sama dengan rekapOpdDetail(), hanya pengelompokannya
     * yang memakai pasiens.kecamatan_id.
     *
     * Tanpa parameter status, yang dikirim adalah seluruh pasien yang punya
     * kebutuhan di kecamatan itu. Dengan ?status=selesai (misalnya), daftarnya
     * dibatasi pada pasien yang punya kebutuhan berstatus itu, dan baris kebutuhan
     * yang ikut dikirim juga hanya yang berstatus itu -- jadi isi modal selalu sama
     * dengan angka yang barusan diklik di tabel rekap.
     *
     * Join ke pasiens dipakai supaya kualifikasi kolomnya sama dengan
     * rekapKelurahan(), dan scope aksesnya tetap scopeDashboard, sehingga user yang
     * ter-scope ke satu wilayah tidak bisa membuka kecamatan lain walau id di URL
     * diganti.
     */
    public function rekapWilayahDetail(Request $request, Kecamatan $kecamatan)
    {
        return $this->wilayahDetail($request, 'pasiens.kecamatan_id', $kecamatan->id, $kecamatan->name);
    }

    /**
     * Rincian pasien untuk satu kelurahan, dipanggil dari baris kelurahan hasil
     * drilldown Rekap Wilayah. Sama seperti rekapWilayahDetail(), hanya
     * pengelompokannya yang memakai pasiens.kelurahan_id.
     */
    public function rekapKelurahanDetail(Request $request, Kelurahan $kelurahan)
    {
        return $this->wilayahDetail($request, 'pasiens.kelurahan_id', $kelurahan->id, $kelurahan->name);
    }

    /**
     * Rincian pasien satu kecamatan untuk modal Rekap Jenis Kebutuhan. Isinya sama
     * dengan rekapWilayahDetail(); bedanya tabel sumbernya punya kolom per jenis
     * kebutuhan, sehingga modalnya biasa dibuka dengan filter ?jenis=.
     */
    public function rekapJenisKebutuhanDetail(Request $request, Kecamatan $kecamatan)
    {
        return $this->wilayahDetail($request, 'pasiens.kecamatan_id', $kecamatan->id, $kecamatan->name);
    }

    /**
     * Rincian pasien satu kelurahan untuk modal Rekap Jenis Kebutuhan, dipanggil
     * dari baris kelurahan hasil drilldown.
     */
    public function rekapJenisKebutuhanKelurahanDetail(Request $request, Kelurahan $kelurahan)
    {
        return $this->wilayahDetail($request, 'pasiens.kelurahan_id', $kelurahan->id, $kelurahan->name);
    }

    /**
     * Isi modal rincian untuk satu wilayah (kecamatan atau kelurahan).
     *
     * Tanpa parameter filter, yang dikirim adalah seluruh pasien yang punya
     * kebutuhan di wilayah itu. Dengan ?status=selesai, ?jenis=2, atau
     * ?kelurahan_id=3 (misalnya), daftarnya dibatasi pada pasien yang punya
     * kebutuhan seperti itu, dan baris kebutuhan yang ikut dikirim juga hanya yang
     * cocok -- jadi isi modal selalu sama dengan angka yang barusan diklik di tabel
     * rekap, termasuk saat tabelnya sedang difilter per kelurahan.
     *
     * Join ke pasiens dipakai supaya kualifikasi kolomnya sama dengan
     * rekapKelurahan(), dan scope aksesnya tetap scopeDashboard, sehingga user yang
     * ter-scope ke satu wilayah tidak bisa membuka wilayah lain walau id di URL
     * diganti.
     */
    private function wilayahDetail(Request $request, string $column, int $id, string $title)
    {
        [$status, $jenis, $kelurahan, $filterLabel] = $this->detailFilters($request);

        $kebutuhans = $this->applyDetailFilters(
            $this->wilayahQuery(MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user()))
                ->where($column, $id),
            $status,
            $jenis
        )
            ->when($kelurahan !== null, fn ($query) => $query->where('pasiens.kelurahan_id', $kelurahan->id))
            ->with([
                'pasien:id,name,nik',
                'jenisKebutuhan:id,name',
                'opd:id,name',
                'jenisPenanganan:id,name',
            ])
            ->withCount('tindakLanjuts')
            ->orderBy('pasien_kebutuhans.id')
            ->get();

        return $this->detailResponse($title, $filterLabel, $kebutuhans);
    }

    /**
     * Filter yang dipakai modal rincian: [status, jenis kebutuhan, kelurahan,
     * label].
     *
     * Label siap pakai untuk subjudul modal, mis. 'Status: Selesai' atau
     * 'Jenis Kebutuhan: Kesehatan · Kelurahan: Sukamaju'. Kalau tidak ada filter,
     * labelnya null dan modal menampilkan 'Semua data'.
     */
    private function detailFilters(Request $request): array
    {
        $status = $this->statusFilter($request);
        $jenis = $this->jenisFilter($request);
        $kelurahan = $this->kelurahanFilter($request);

        $labels = [];
        if ($status !== null) {
            $labels[] = 'Status: ' . PasienKebutuhan::STATUS_LABELS[$status];
        }
        if ($jenis !== null) {
            $labels[] = 'Jenis Kebutuhan: ' . $jenis->name;
        }
        if ($kelurahan !== null) {
            $labels[] = 'Kelurahan: ' . $kelurahan->name;
        }

        return [$status, $jenis, $kelurahan, $labels ? implode(' · ', $labels) : null];
    }

    /**
     * Menerapkan filter modal ke query kebutuhan.
     */
    private function applyDetailFilters($query, ?string $status, ?JenisKebutuhan $jenis)
    {
        return $query
            ->when($status !== null, fn ($q) => $q->where('pasien_kebutuhans.verification_status', $status))
            ->when($jenis !== null, fn ($q) => $q->where('pasien_kebutuhans.jenis_kebutuhan_id', $jenis->id));
    }

    /**
     * Status yang diminta lewat ?status=, atau null kalau kosong/tidak dikenal.
     *
     * Nilai yang tidak dikenal sengaja diperlakukan sebagai "semua status", bukan
     * error, supaya URL yang diubah tangan tidak memecahkan modal.
     */
    private function statusFilter(Request $request): ?string
    {
        $status = (string) $request->query('status', '');

        return array_key_exists($status, PasienKebutuhan::STATUS_LABELS) ? $status : null;
    }

    /**
     * Jenis kebutuhan yang diminta lewat ?jenis=, atau null kalau kosong/tidak ada.
     * Barisnya diambil sekalian supaya namanya bisa dipakai sebagai label modal.
     */
    private function jenisFilter(Request $request): ?JenisKebutuhan
    {
        $id = (int) $request->query('jenis', 0);

        return $id > 0 ? JenisKebutuhan::query()->find($id) : null;
    }

    /**
     * Kelurahan yang diminta lewat ?kelurahan_id=, atau null kalau kosong/tidak ada.
     *
     * Dipakai saat tabelnya sedang difilter per kelurahan: modal yang dibuka dari
     * baris kecamatan ikut dibatasi ke kelurahan itu, supaya isinya sama dengan
     * angka yang tertera di barisnya.
     */
    private function kelurahanFilter(Request $request): ?Kelurahan
    {
        $id = (int) $request->query('kelurahan_id', 0);

        return $id > 0 ? Kelurahan::query()->find($id) : null;
    }

    /**
     * Mengelompokkan baris kebutuhan per pasien untuk modal rincian.
     *
     * Satu pasien bisa punya beberapa kebutuhan (mis. Kesehatan dan Pendidikan),
     * jadi barisnya dikelompokkan per pasien dan daftar kebutuhannya dikirim
     * sekaligus -- modal tidak perlu request lagi saat baris pasien dibuka.
     */
    private function patientSummary(Collection $kebutuhans): Collection
    {
        return $kebutuhans
            ->groupBy('pasien_id')
            ->map(function (Collection $items) {
                $pasien = $items->first()->pasien;

                return [
                    'id'          => $pasien?->id,
                    'name'        => $pasien?->name ?? '-',
                    'nik'         => $pasien?->nik,
                    'kebutuhans'  => $items->map(fn (PasienKebutuhan $item) => [
                        'id'                  => $item->id,
                        'jenis_kebutuhan'     => $item->jenisKebutuhan?->name ?? '-',
                        'opd'                 => $item->opd?->name ?? '-',
                        'jenis_penanganan'    => $item->jenisPenanganan?->name ?? '-',
                        'status_key'          => $item->verification_status,
                        'status'              => PasienKebutuhan::STATUS_LABELS[$item->verification_status] ?? $item->verification_status,
                        'tindak_lanjut_count' => (int) $item->tindak_lanjuts_count,
                        'url'                 => route('tindak-lanjut.show', $item->id),
                    ])->values(),
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Payload JSON modal rincian. Bentuknya sama untuk semua tabel rekap supaya
     * satu partial view bisa melayani semuanya.
     */
    private function detailResponse(string $title, ?string $filterLabel, Collection $kebutuhans)
    {
        return response()->json([
            'title'        => $title,
            'filter_label' => $filterLabel,
            'patients'     => $this->patientSummary($kebutuhans),
        ]);
    }

    /**
     * Rekap tabel per kecamatan menurut jenis kebutuhan. Satu kolom per jenis
     * kebutuhan, dibaca dari master data supaya jenis yang ditambah belakangan
     * langsung ikut tampil tanpa perubahan kode.
     *
     * Total Pasien memakai semantik yang sama dengan tabel rekap lain
     * (COUNT(DISTINCT pasien_id)), sedangkan kolom jenis menghitung baris
     * kebutuhan. Karena satu pasien bisa punya beberapa baris kebutuhan, jumlah
     * kolom jenis boleh lebih besar dari Total Pasien, dan baris kebutuhan yang
     * jenis_kebutuhan_id-nya kosong hanya terhitung di Total Pasien.
     *
     * Barisnya sama seperti Rekap Wilayah: seluruh kecamatan di master data ikut
     * ditampilkan, yang belum ada datanya bernilai 0.
     */
    public function rekapJenisKebutuhan(Request $request)
    {
        $baseQuery = MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user());
        $jenis = $this->jenisKebutuhanList();
        [$kecamatanId, $kelurahanId] = $this->wilayahFilterIds($request);

        $kecamatans = $this->wilayahFilterOptions($request->user());

        // Sama seperti Rekap Wilayah: baris mengikuti filter yang aktif, dan
        // kecamatan yang dipilih tetap tampil walau datanya kosong.
        $rows = $this->applyWilayahFilter($this->wilayahQuery($baseQuery), $kecamatanId, $kelurahanId)
            ->selectRaw('pasiens.kecamatan_id as wilayah_id, ' . $this->kebutuhanColumns($jenis))
            ->groupBy('pasiens.kecamatan_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $kecamatanId > 0
            ? $kecamatans->only([$kecamatanId])
            : $this->kecamatanNames($request->user(), $rows);

        return view('statistik.rekap-jenis-kebutuhan', [
            'jenisKebutuhans'  => $jenis,
            'kecamatanSummary' => $names
                ->map(fn ($name, $id) => $this->rekapKebutuhanRow($rows->get($id), $name, $jenis, (int) $id))
                ->values(),
            'totals'           => $this->rekapKebutuhanTotals(
                $this->applyWilayahFilter($this->wilayahQuery($baseQuery), $kecamatanId, $kelurahanId),
                $jenis
            ),
            'filters'          => ['kecamatan_id' => $kecamatanId, 'kelurahan_id' => $kelurahanId],
            'kecamatans'       => $kecamatans,
            'kelurahans'       => $this->kelurahanOptions($request->user(), $kecamatans),
        ]);
    }

    /**
     * Rincian per kelurahan untuk satu kecamatan pada Rekap Jenis Kebutuhan,
     * dipanggil lewat AJAX saat baris kecamatan diklik.
     *
     * Daftar kelurahannya juga dari master data, sama seperti rekapKelurahan().
     *
     * Sama seperti rekapKelurahan(): scope dan filter kecamatan berlaku
     * bersamaan, jadi user yang ter-scope ke satu wilayah tetap tidak bisa
     * melihat wilayah lain walau id kecamatan di URL diganti.
     */
    public function rekapJenisKebutuhanKelurahan(Request $request, Kecamatan $kecamatan)
    {
        $baseQuery = MobileAccessScope::scopeDashboard(PasienKebutuhan::query(), $request->user());
        $jenis = $this->jenisKebutuhanList();
        $kelurahanId = (int) $request->query('kelurahan_id', 0);

        $rows = $this->wilayahQuery($baseQuery)
            ->selectRaw('pasiens.kelurahan_id as wilayah_id, ' . $this->kebutuhanColumns($jenis))
            ->where('pasiens.kecamatan_id', $kecamatan->id)
            // Filter kelurahan di tabel ikut berlaku di sini, sama seperti
            // rekapKelurahan().
            ->when($kelurahanId > 0, fn ($query) => $query->where('pasiens.kelurahan_id', $kelurahanId))
            ->groupBy('pasiens.kelurahan_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $this->kelurahanNames($request->user(), $rows, $kecamatan);

        return response()->json([
            'kecamatan' => $kecamatan->name,
            'rows'      => $names
                ->map(fn ($name, $id) => $this->rekapKebutuhanRow($rows->get($id), $name, $jenis, (int) $id))
                ->values()
                ->all(),
        ]);
    }

    /**
     * Ekspresi agregat tabel rekap. Dipakai bersama oleh Rekap OPD, Rekap Wilayah,
     * dan rincian kelurahan supaya definisi kolomnya tidak bisa menyimpang.
     *
     * Semantiknya mengikuti tabel Dashboard: kolom Total Pasien memakai
     * COUNT(DISTINCT pasien_id) sedangkan kolom status menghitung baris kebutuhan.
     * Karena itu Total Pasien tidak selalu sama dengan jumlah kelima kolom status --
     * satu pasien bisa punya beberapa baris kebutuhan.
     */
    private function rekapColumns(): string
    {
        return "COUNT(DISTINCT pasien_id) as total_pasien,
            SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN verification_status = 'proses' THEN 1 ELSE 0 END) as proses_count,
            SUM(CASE WHEN verification_status = 'pending_bantuan' THEN 1 ELSE 0 END) as pending_bantuan_count,
            SUM(CASE WHEN verification_status = 'tidak_layak' THEN 1 ELSE 0 END) as tidak_layak_count,
            SUM(CASE WHEN verification_status = 'selesai' THEN 1 ELSE 0 END) as selesai_count";
    }

    /**
     * Base query kebutuhan yang di-join ke pasiens, untuk pengelompokan per
     * wilayah. Join-nya tidak menggandakan hitungan karena satu baris kebutuhan
     * hanya mengarah ke satu pasien, dan pasien_id bersifat wajib.
     */
    private function wilayahQuery($baseQuery)
    {
        return (clone $baseQuery)->join('pasiens', 'pasiens.id', '=', 'pasien_kebutuhans.pasien_id');
    }

    /**
     * Menyeragamkan satu baris agregat jadi array siap dipakai view.
     *
     * Barisnya boleh null: wilayah yang belum punya data tetap ditampilkan dengan
     * nilai 0. Id-nya karena itu bisa disebut terpisah, sebab tidak ada barisnya
     * untuk dibaca.
     */
    private function rekapRow($row, string $name, ?int $id = null): array
    {
        return [
            'id'                    => $id ?? (int) $row->wilayah_id,
            'name'                  => $name,
            'total_pasien'          => (int) ($row->total_pasien ?? 0),
            'pending_count'         => (int) ($row->pending_count ?? 0),
            'proses_count'          => (int) ($row->proses_count ?? 0),
            'pending_bantuan_count' => (int) ($row->pending_bantuan_count ?? 0),
            'tidak_layak_count'     => (int) ($row->tidak_layak_count ?? 0),
            'selesai_count'         => (int) ($row->selesai_count ?? 0),
        ];
    }

    /**
     * Daftar OPD yang boleh dilihat user, dikunci menurut id dan diurut menurut nama.
     * Dipakai Rekap OPD dan grafik batang OPD supaya keduanya melisting OPD yang sama.
     *
     * Admin melihat seluruh OPD di master data; user lain dibatasi supaya halaman ini
     * tidak diam-diam membuka daftar OPD di luar jangkauannya, sementara OPD-nya
     * sendiri tetap muncul walau belum ada satu pun baris kebutuhan (role OPD memang
     * ter-scope ke satu OPD, jadi barisnya tidak akan pernah ikut lewat $rows).
     */
    private function opdNames(User $user, Collection $rows): Collection
    {
        $roles = collect(MobileAccessScope::roles($user));

        if ($roles->contains('admin')) {
            return Opd::query()->orderBy('name')->pluck('name', 'id');
        }

        $names = Opd::query()
            ->whereIn('id', $rows->pluck('wilayah_id')->all())
            ->pluck('name', 'id');

        if ($roles->contains('opd') && $user->opd_id) {
            $names = $names->union(Opd::query()->whereKey($user->opd_id)->pluck('name', 'id'));
        }

        return $names->sort();
    }

    /**
     * Daftar kecamatan yang boleh dilihat user, dikunci menurut id dan diurut menurut
     * nama. Bentuknya sama dengan opdNames(): admin melihat seluruh kecamatan di
     * master data, user lain dibatasi pada kecamatan yang sudah muncul di datanya
     * plus kecamatannya sendiri kalau dia memang ter-scope ke satu kecamatan.
     */
    private function kecamatanNames(User $user, Collection $rows): Collection
    {
        $roles = collect(MobileAccessScope::roles($user));

        if ($roles->contains('admin')) {
            return Kecamatan::query()->orderBy('name')->pluck('name', 'id');
        }

        $names = Kecamatan::query()
            ->whereIn('id', $rows->pluck('wilayah_id')->all())
            ->pluck('name', 'id');

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            $names = $names->union(Kecamatan::query()->whereKey($user->kecamatan_id)->pluck('name', 'id'));
        }

        return $names->sort();
    }

    /**
     * Filter wilayah yang diminta lewat query string: [kecamatan_id, kelurahan_id].
     * Nilai 0 berarti filter itu tidak dipakai.
     *
     * Kelurahan yang dipilih tanpa kecamatan (mis. URL diketik tangan) tetap
     * membawa kecamatannya: baris kecamatan itulah yang harus muncul di tabel,
     * karena kelurahan tidak pernah ditampilkan sebagai baris induk. Kelurahan
     * yang tidak ada, atau yang bukan milik kecamatan yang dipilih, diabaikan.
     */
    private function wilayahFilterIds(Request $request): array
    {
        $kecamatanId = (int) $request->query('kecamatan_id', 0);
        $kelurahanId = (int) $request->query('kelurahan_id', 0);

        if ($kelurahanId > 0) {
            $kelurahan = Kelurahan::query()->find($kelurahanId);

            if ($kelurahan === null || ($kecamatanId > 0 && $kecamatanId !== (int) $kelurahan->kecamatan_id)) {
                $kelurahanId = 0;
            } else {
                $kecamatanId = (int) $kelurahan->kecamatan_id;
            }
        }

        return [$kecamatanId, $kelurahanId];
    }

    /**
     * Menerapkan filter wilayah ke query agregat (yang sudah di-join ke pasiens).
     */
    private function applyWilayahFilter($query, int $kecamatanId, int $kelurahanId)
    {
        return $query
            ->when($kecamatanId > 0, fn ($q) => $q->where('pasiens.kecamatan_id', $kecamatanId))
            ->when($kelurahanId > 0, fn ($q) => $q->where('pasiens.kelurahan_id', $kelurahanId));
    }

    /**
     * Opsi filter kecamatan: seluruh master data, dibatasi untuk user yang
     * ter-scope ke satu kecamatan/kelurahan supaya pilihannya tidak membuka
     * wilayah di luar jangkauannya.
     */
    private function wilayahFilterOptions(User $user): Collection
    {
        $roles = collect(MobileAccessScope::roles($user));

        $query = Kecamatan::query()->orderBy('name');

        if ($roles->contains('kecamatan') && $user->kecamatan_id) {
            $query->whereKey($user->kecamatan_id);
        } elseif ($roles->contains('kelurahan') && $user->kelurahan_id) {
            $query->whereKey(Kelurahan::query()->whereKey($user->kelurahan_id)->value('kecamatan_id'));
        }

        return $query->pluck('name', 'id');
    }

    /**
     * Opsi filter kelurahan, dikelompokkan per kecamatan supaya form bisa
     * merangkainya: kelurahan hanya muncul setelah kecamatannya dipilih.
     */
    private function kelurahanOptions(User $user, Collection $kecamatans): array
    {
        $roles = collect(MobileAccessScope::roles($user));

        $query = Kelurahan::query()
            ->whereIn('kecamatan_id', $kecamatans->keys()->all())
            ->orderBy('name');

        // User kelurahan hanya boleh memilih kelurahannya sendiri.
        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            $query->whereKey($user->kelurahan_id);
        }

        return $query->get(['id', 'name', 'kecamatan_id'])
            ->groupBy('kecamatan_id')
            ->map(fn (Collection $items) => $items
                ->map(fn (Kelurahan $item) => ['id' => $item->id, 'name' => $item->name])
                ->values()
                ->all())
            ->all();
    }

    /**
     * Daftar kelurahan yang boleh dilihat user di dalam satu kecamatan, dikunci
     * menurut id dan diurut menurut nama.
     *
     * Sama seperti dua helper di atas: admin melihat seluruh kelurahan kecamatan itu
     * dari master data, user lain dibatasi pada kelurahan yang sudah muncul di
     * datanya plus kelurahannya sendiri kalau dia memang ter-scope ke satu kelurahan.
     */
    private function kelurahanNames(User $user, Collection $rows, Kecamatan $kecamatan): Collection
    {
        $roles = collect(MobileAccessScope::roles($user));

        if ($roles->contains('admin')) {
            return Kelurahan::query()
                ->where('kecamatan_id', $kecamatan->id)
                ->orderBy('name')
                ->pluck('name', 'id');
        }

        $names = Kelurahan::query()
            ->whereIn('id', $rows->pluck('wilayah_id')->all())
            ->pluck('name', 'id');

        if ($roles->contains('kelurahan') && $user->kelurahan_id) {
            $names = $names->union(Kelurahan::query()->whereKey($user->kelurahan_id)->pluck('name', 'id'));
        }

        return $names->sort();
    }

    /**
     * Baris total keseluruhan untuk footer tabel rekap.
     *
     * Dihitung lewat query terpisah, bukan dengan menjumlahkan kolom Total Pasien
     * tiap baris: satu pasien bisa muncul di beberapa OPD, jadi penjumlahan kolom
     * akan menghitung orang yang sama berkali-kali. Kolom status sendiri aman
     * dijumlahkan karena setiap baris kebutuhan hanya milik satu OPD.
     *
     * Untuk wilayah, kolomnya justru konsisten: setiap pasien hanya punya satu
     * kecamatan, jadi jumlah kolom per kecamatan sama dengan totalnya.
     */
    private function rekapTotals($query): array
    {
        $row = $query->selectRaw($this->rekapColumns())->first();

        return [
            'total_pasien'          => (int) ($row->total_pasien ?? 0),
            'pending_count'         => (int) ($row->pending_count ?? 0),
            'proses_count'          => (int) ($row->proses_count ?? 0),
            'pending_bantuan_count' => (int) ($row->pending_bantuan_count ?? 0),
            'tidak_layak_count'     => (int) ($row->tidak_layak_count ?? 0),
            'selesai_count'         => (int) ($row->selesai_count ?? 0),
        ];
    }

    /**
     * Daftar jenis kebutuhan sebagai kolom tabel: nama dikunci menurut id,
     * diurutkan menurut id supaya urutan kolomnya mengikuti urutan input di Data
     * Master dan tidak berpindah sendiri.
     */
    private function jenisKebutuhanList()
    {
        return JenisKebutuhan::query()->orderBy('id')->pluck('name', 'id');
    }

    /**
     * Ekspresi agregat tabel Rekap Jenis Kebutuhan: satu kolom SUM per jenis
     * kebutuhan, ditambah Total Pasien yang semantiknya sama dengan tabel rekap
     * lain. Dipakai bersama oleh tabel kecamatan dan rincian kelurahan supaya
     * definisi kolomnya tidak bisa menyimpang.
     *
     * Id jenis kebutuhan berasal dari master data dan di-cast ke int, jadi tidak
     * ada nilai dari pengguna yang masuk ke SQL.
     */
    private function kebutuhanColumns($jenis): string
    {
        $columns = ['COUNT(DISTINCT pasien_id) as total_pasien'];

        foreach ($jenis->keys() as $id) {
            $id = (int) $id;

            $columns[] = "SUM(CASE WHEN jenis_kebutuhan_id = {$id} THEN 1 ELSE 0 END) as kebutuhan_{$id}_count";
        }

        return implode(",\n            ", $columns);
    }

    /**
     * Menyeragamkan satu baris agregat jadi array siap dipakai view.
     *
     * Barisnya boleh null, sama seperti rekapRow(): wilayah yang belum punya data
     * tetap ditampilkan dengan seluruh hitungannya 0, dan id-nya disebut terpisah
     * karena tidak ada barisnya untuk dibaca.
     */
    private function rekapKebutuhanRow($row, string $name, $jenis, ?int $id = null): array
    {
        return [
            'id'           => $id ?? (int) $row->wilayah_id,
            'name'         => $name,
            'total_pasien' => (int) ($row->total_pasien ?? 0),
            'counts'       => $this->kebutuhanCounts($row, $jenis),
        ];
    }

    /**
     * Hitungan per jenis kebutuhan dari satu baris agregat, dikunci menurut id
     * jenis kebutuhan.
     *
     * Baris yang null tetap menghasilkan satu entri 0 per jenis kebutuhan, supaya
     * wilayah tanpa data punya bentuk baris yang sama persis dengan wilayah berdata.
     */
    private function kebutuhanCounts($row, $jenis): array
    {
        $counts = [];

        foreach ($jenis->keys() as $id) {
            $counts[(int) $id] = (int) ($row->{"kebutuhan_{$id}_count"} ?? 0);
        }

        return $counts;
    }

    /**
     * Baris total keseluruhan untuk footer tabel Rekap Jenis Kebutuhan.
     *
     * Total Pasien dihitung lewat query terpisah, bukan menjumlahkan kolom tiap
     * baris, karena alasannya sama dengan rekapTotals(): satu pasien bisa muncul
     * di beberapa kecamatan. Kolom jenis kebutuhan sendiri aman dijumlahkan --
     * setiap baris kebutuhan hanya milik satu wilayah.
     */
    private function rekapKebutuhanTotals($query, $jenis): array
    {
        $row = $query->selectRaw($this->kebutuhanColumns($jenis))->first();

        if (!$row) {
            return ['total_pasien' => 0, 'counts' => []];
        }

        return [
            'total_pasien' => (int) $row->total_pasien,
            'counts'       => $this->kebutuhanCounts($row, $jenis),
        ];
    }

    /**
     * Jumlah kebutuhan per OPD, dipecah per verification_status. Seluruh status
     * dikirim sekaligus supaya dropdown "jenis proses" cukup menukar dataset di
     * sisi klien tanpa reload maupun endpoint tambahan.
     */
    private function opdChart($baseQuery, User $user): array
    {
        $statuses = array_keys(PasienKebutuhan::STATUS_LABELS);

        // Nilai status berasal dari konstanta STATUS_LABELS, bukan input pengguna.
        $columns = ['opd_id', 'COUNT(*) as total'];
        foreach ($statuses as $status) {
            $columns[] = "SUM(CASE WHEN verification_status = '{$status}' THEN 1 ELSE 0 END) as {$status}_count";
        }

        $rows = (clone $baseQuery)
            ->selectRaw(implode(', ', $columns))
            ->whereNotNull('opd_id')
            ->groupBy('opd_id')
            ->get();

        $series = ['total' => []];
        foreach ($statuses as $status) {
            $series[$status] = [];
        }

        // Tidak ada satu pun baris kebutuhan: grafiknya tetap digambar dengan nilai 0
        // untuk OPD yang boleh dilihat user, supaya sumbu dan labelnya tetap terbaca.
        // Kalau labelnya dikosongkan, yang tampil cuma kanvas kosong tanpa keterangan.
        if ($rows->isEmpty()) {
            $allNames = $this->opdNames($user, $rows);

            foreach ($series as $key => $values) {
                $series[$key] = array_fill(0, $allNames->count(), 0);
            }

            return ['labels' => $allNames->values()->all(), 'series' => $series];
        }

        $names = Opd::query()
            ->whereIn('id', $rows->pluck('opd_id')->all())
            ->pluck('name', 'id');

        // Diurutkan menurut nama OPD, bukan menurut total: kalau diurut total,
        // posisi batang berpindah setiap kali pengguna mengganti jenis proses.
        $sorted = $rows->sortBy(fn ($row) => $names[$row->opd_id] ?? '')->values();

        foreach ($sorted as $row) {
            $series['total'][] = (int) $row->total;
            foreach ($statuses as $status) {
                $series[$status][] = (int) $row->{"{$status}_count"};
            }
        }

        return [
            'labels' => $sorted->map(fn ($row) => $names[$row->opd_id] ?? '-')->all(),
            'series' => $series,
        ];
    }

    /**
     * Grafik line bulanan menurut jenis proses (verification_status).
     */
    private function statusMonthlyChart($baseQuery): array
    {
        $buckets = $this->monthBuckets();

        // Semua status dan semua bulan disiapkan bernilai 0 lebih dulu, supaya
        // bulan tanpa data tetap tampil sebagai titik nol, bukan garis terputus.
        $pivot = [];
        foreach (array_keys(PasienKebutuhan::STATUS_LABELS) as $status) {
            $pivot[$status] = array_fill_keys(array_keys($buckets), 0);
        }

        $rows = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, verification_status as grup, COUNT(*) as total")
            ->where('created_at', '>=', $this->windowStart())
            ->groupBy('bulan', 'grup')
            ->get();

        foreach ($rows as $row) {
            // Status di luar peta label (mis. sisa data lama) dilewati agar tidak
            // memunculkan seri tanpa nama.
            if (!isset($pivot[$row->grup][$row->bulan])) {
                continue;
            }

            $pivot[$row->grup][$row->bulan] = (int) $row->total;
        }

        $datasets = [];
        foreach (PasienKebutuhan::STATUS_LABELS as $status => $label) {
            $datasets[] = [
                'label' => $label,
                'data'  => array_values($pivot[$status]),
            ];
        }

        // Lima seri status dikirim lengkap walaupun tidak ada datanya, jadi grafiknya
        // tetap tergambar sebagai lima garis nol di sepanjang 12 bulan.
        return [
            'labels'   => array_values($buckets),
            'datasets' => $datasets,
        ];
    }

    /**
     * Grafik line bulanan menurut jenis kebutuhan.
     */
    private function kebutuhanMonthlyChart($baseQuery): array
    {
        $buckets = $this->monthBuckets();

        $rows = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, jenis_kebutuhan_id as grup, COUNT(*) as total")
            ->where('created_at', '>=', $this->windowStart())
            ->whereNotNull('jenis_kebutuhan_id')
            ->groupBy('bulan', 'grup')
            ->get();

        // Tanpa data sama sekali: seluruh jenis kebutuhan dari master data tetap
        // digambar sebagai garis nol, supaya label bulannya dan legendanya muncul.
        if ($rows->isEmpty()) {
            $datasets = JenisKebutuhan::query()
                ->orderBy('id')
                ->pluck('name', 'id')
                ->map(fn ($name) => [
                    'label' => $name,
                    'data'  => array_fill(0, count($buckets), 0),
                ])
                ->values()
                ->all();

            return ['labels' => array_values($buckets), 'datasets' => $datasets];
        }

        $pivot = [];
        $totals = [];
        foreach ($rows as $row) {
            $grup = (int) $row->grup;
            $pivot[$grup][$row->bulan] = (int) $row->total;
            $totals[$grup] = ($totals[$grup] ?? 0) + (int) $row->total;
        }

        // Seri terbesar lebih dulu supaya mendapat warna paling jelas di legenda.
        arsort($totals);

        $names = JenisKebutuhan::query()
            ->whereIn('id', array_keys($totals))
            ->pluck('name', 'id');

        $datasets = [];
        foreach (array_keys($totals) as $id) {
            $datasets[] = [
                'label' => $names[$id] ?? '-',
                'data'  => array_map(fn ($bulan) => $pivot[$id][$bulan] ?? 0, array_keys($buckets)),
            ];
        }

        return [
            'labels'   => array_values($buckets),
            'datasets' => $datasets,
        ];
    }

    /**
     * Grafik pie total kebutuhan per jenis kebutuhan (sepanjang data, tidak
     * dibatasi 12 bulan seperti grafik line).
     */
    private function kebutuhanPieChart($baseQuery): array
    {
        $rows = (clone $baseQuery)
            ->selectRaw('jenis_kebutuhan_id, COUNT(*) as total')
            ->whereNotNull('jenis_kebutuhan_id')
            ->groupBy('jenis_kebutuhan_id')
            ->orderByDesc('total')
            ->get();

        // Tanpa data: seluruh jenis kebutuhan dari master data dikirim dengan nilai 0.
        // Irisan pie-nya memang tidak terlihat, tapi legendanya tetap tampil sehingga
        // kartunya tidak tampak seperti kanvas yang gagal dimuat.
        if ($rows->isEmpty()) {
            $all = JenisKebutuhan::query()->orderBy('id')->pluck('name', 'id');

            return [
                'labels' => $all->values()->all(),
                'data'   => array_fill(0, $all->count(), 0),
            ];
        }

        $names = JenisKebutuhan::query()
            ->whereIn('id', $rows->pluck('jenis_kebutuhan_id')->all())
            ->pluck('name', 'id');

        return [
            'labels' => $rows->map(fn ($row) => $names[$row->jenis_kebutuhan_id] ?? '-')->all(),
            'data'   => $rows->map(fn ($row) => (int) $row->total)->all(),
        ];
    }

    /**
     * Kunci 'Y-m' untuk 12 bulan terakhir beserta labelnya, mis. 'Agu 25'.
     *
     * Tiap bulan dihitung dari offset, bukan dengan memajukan satu variabel
     * kursor. Carbon di aplikasi ini adalah CarbonImmutable (lihat Date::use()
     * di AppServiceProvider), jadi addMonth() mengembalikan instance baru dan
     * tidak mengubah variabelnya. Saat masih ditulis sebagai $cursor->addMonth();
     * seluruh 12 iterasi menulis key yang sama, sehingga kedua grafik tren
     * bulanan hanya kebagian satu label dan satu titik nol.
     */
    private function monthBuckets(): array
    {
        $buckets = [];
        $start = $this->windowStart();

        for ($i = 0; $i < self::MONTHS; $i++) {
            $month = $start->addMonths($i);

            $buckets[$month->format('Y-m')] = self::MONTH_ABBR[(int) $month->format('n')] . ' ' . $month->format('y');
        }

        return $buckets;
    }

    /**
     * Awal jendela 12 bulan: awal bulan, 11 bulan sebelum bulan berjalan.
     * Sengaja di-startOfMonth dulu supaya penambahan bulan tidak melompat
     * (tanggal 31 + 1 bulan bisa melewati bulan berikutnya).
     */
    private function windowStart()
    {
        return now()->startOfMonth()->subMonths(self::MONTHS - 1);
    }
}
