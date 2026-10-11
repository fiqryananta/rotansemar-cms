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
            'opdSummary' => $names
                ->map(fn ($name, $id) => $this->rekapRow($rows->get($id), $name, (int) $id))
                ->values(),
            'totals'     => $this->rekapTotals((clone $baseQuery)->whereNotNull('opd_id')),
        ]);
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

        $rows = $this->wilayahQuery($baseQuery)
            ->selectRaw('pasiens.kecamatan_id as wilayah_id, ' . $this->rekapColumns())
            ->groupBy('pasiens.kecamatan_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $this->kecamatanNames($request->user(), $rows);

        return view('statistik.rekap-wilayah', [
            'kecamatanSummary' => $names
                ->map(fn ($name, $id) => $this->rekapRow($rows->get($id), $name, (int) $id))
                ->values(),
            'totals'           => $this->rekapTotals($this->wilayahQuery($baseQuery)),
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

        $rows = $this->wilayahQuery($baseQuery)
            ->selectRaw('pasiens.kelurahan_id as wilayah_id, ' . $this->rekapColumns())
            ->where('pasiens.kecamatan_id', $kecamatan->id)
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

        $rows = $this->wilayahQuery($baseQuery)
            ->selectRaw('pasiens.kecamatan_id as wilayah_id, ' . $this->kebutuhanColumns($jenis))
            ->groupBy('pasiens.kecamatan_id')
            ->get()
            ->keyBy('wilayah_id');

        $names = $this->kecamatanNames($request->user(), $rows);

        return view('statistik.rekap-jenis-kebutuhan', [
            'jenisKebutuhans'  => $jenis,
            'kecamatanSummary' => $names
                ->map(fn ($name, $id) => $this->rekapKebutuhanRow($rows->get($id), $name, $jenis, (int) $id))
                ->values(),
            'totals'           => $this->rekapKebutuhanTotals($this->wilayahQuery($baseQuery), $jenis),
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

        $rows = $this->wilayahQuery($baseQuery)
            ->selectRaw('pasiens.kelurahan_id as wilayah_id, ' . $this->kebutuhanColumns($jenis))
            ->where('pasiens.kecamatan_id', $kecamatan->id)
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
