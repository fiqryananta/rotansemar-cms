<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaskesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisKebutuhanController;
use App\Http\Controllers\JenisPenangananController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KegiatanPenyuluhanController;
use App\Http\Controllers\KelurahanController;
use App\Http\Controllers\PuskesmasController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MedicationPickupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    // User Management
    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:users.view')
        ->middlewareFor(['create', 'store'], 'permission:users.create')
        ->middlewareFor(['edit', 'update'], 'permission:users.edit')
        ->middlewareFor('destroy', 'permission:users.delete');

    // Role Management
    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:roles.view')
        ->middlewareFor(['create', 'store'], 'permission:roles.create')
        ->middlewareFor(['edit', 'update'], 'permission:roles.edit')
        ->middlewareFor('destroy', 'permission:roles.delete');

    // Permission Management
    Route::resource('permissions', PermissionController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:permissions.view')
        ->middlewareFor(['create', 'store'], 'permission:permissions.create')
        ->middlewareFor(['edit', 'update'], 'permission:permissions.edit')
        ->middlewareFor('destroy', 'permission:permissions.delete');

    // OPD Management
    Route::resource('opds', OpdController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:opds.view')
        ->middlewareFor(['create', 'store'], 'permission:opds.create')
        ->middlewareFor(['edit', 'update'], 'permission:opds.edit')
        ->middlewareFor('destroy', 'permission:opds.delete');

    // Pekerjaan Management
    Route::resource('pekerjaan', PekerjaanController::class)
        ->parameters(['pekerjaan' => 'pekerjaan'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:pekerjaan.view')
        ->middlewareFor(['create', 'store'], 'permission:pekerjaan.create')
        ->middlewareFor(['edit', 'update'], 'permission:pekerjaan.edit')
        ->middlewareFor('destroy', 'permission:pekerjaan.delete');

    // Faskes Management
    Route::resource('faskes', FaskesController::class)
        ->parameters(['faskes' => 'faskes'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:faskes.view')
        ->middlewareFor(['create', 'store'], 'permission:faskes.create')
        ->middlewareFor(['edit', 'update'], 'permission:faskes.edit')
        ->middlewareFor('destroy', 'permission:faskes.delete');

    // Kecamatan Management
    Route::resource('kecamatans', KecamatanController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:kecamatans.view')
        ->middlewareFor(['create', 'store'], 'permission:kecamatans.create')
        ->middlewareFor(['edit', 'update'], 'permission:kecamatans.edit')
        ->middlewareFor('destroy', 'permission:kecamatans.delete');

    // Kelurahan Management
    Route::resource('kelurahans', KelurahanController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:kelurahans.view')
        ->middlewareFor(['create', 'store'], 'permission:kelurahans.create')
        ->middlewareFor(['edit', 'update'], 'permission:kelurahans.edit')
        ->middlewareFor('destroy', 'permission:kelurahans.delete');

    // Puskesmas Management
    Route::resource('puskesmas', PuskesmasController::class)
        ->parameters(['puskesmas' => 'puskesmas'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:puskesmas.view')
        ->middlewareFor(['create', 'store'], 'permission:puskesmas.create')
        ->middlewareFor(['edit', 'update'], 'permission:puskesmas.edit')
        ->middlewareFor('destroy', 'permission:puskesmas.delete');

    // Jenis Penanganan Management
    Route::resource('jenis-penanganans', JenisPenangananController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:jenis-penanganans.view')
        ->middlewareFor(['create', 'store'], 'permission:jenis-penanganans.create')
        ->middlewareFor(['edit', 'update'], 'permission:jenis-penanganans.edit')
        ->middlewareFor('destroy', 'permission:jenis-penanganans.delete');

    // Jenis Kebutuhan Management
    Route::resource('jenis-kebutuhans', JenisKebutuhanController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:jenis-kebutuhans.view')
        ->middlewareFor(['create', 'store'], 'permission:jenis-kebutuhans.create')
        ->middlewareFor(['edit', 'update'], 'permission:jenis-kebutuhans.edit')
        ->middlewareFor('destroy', 'permission:jenis-kebutuhans.delete');

    // Pasien Management
    Route::resource('pasiens', PasienController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:pasiens.view')
        ->middlewareFor(['create', 'store'], 'permission:pasiens.create')
        ->middlewareFor(['edit', 'update'], 'permission:pasiens.edit')
        ->middlewareFor('destroy', 'permission:pasiens.delete');
    Route::get('pasiens/import', [PasienController::class, 'importPage'])
        ->middleware(['permission:pasiens.create'])
        ->name('pasiens.import.index');
    Route::post('pasiens/import', [PasienController::class, 'importStore'])
        ->middleware(['permission:pasiens.create', 'throttle:10,1'])
        ->name('pasiens.import.store');
    Route::get('pasiens/import/template', [PasienController::class, 'importTemplate'])
        ->middleware(['permission:pasiens.create'])
        ->name('pasiens.import.template');
    Route::get('pasiens/{pasien}', [PasienController::class, 'show'])
        ->middleware('permission:pasiens.view')
        ->name('pasiens.show');

    // Tindak Lanjut
    Route::prefix('tindak-lanjut')->name('tindak-lanjut.')->group(function () {
        Route::get('/', [TindakLanjutController::class, 'index'])
            ->middleware('permission:tindak-lanjut.view')
            ->name('index');
        Route::get('/{pasienKebutuhan}', [TindakLanjutController::class, 'show'])
            ->middleware('permission:tindak-lanjut.view')
            ->name('show');
        Route::patch('/{pasienKebutuhan}/verifikasi', [TindakLanjutController::class, 'verifikasi'])
            ->middleware('permission:tindak-lanjut.verifikasi')
            ->name('verifikasi');
        Route::post('/{pasienKebutuhan}/riwayat', [TindakLanjutController::class, 'tambahRiwayat'])
            ->middleware('permission:tindak-lanjut.riwayat.create')
            ->name('tambahRiwayat');
        Route::patch('/{pasienKebutuhan}/selesai', [TindakLanjutController::class, 'selesai'])
            ->middleware('permission:tindak-lanjut.selesai')
            ->name('selesai');
    });

    // Pengambilan Obat
    Route::resource('medication-pickups', MedicationPickupController::class)
        ->parameters(['medication-pickups' => 'medicationPickup'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:medication-pickups.view')
        ->middlewareFor(['create', 'store'], 'permission:medication-pickups.create')
        ->middlewareFor(['edit', 'update'], 'permission:medication-pickups.edit')
        ->middlewareFor('destroy', 'permission:medication-pickups.delete');

    // Laporan
    Route::get('laporan', [LaporanController::class, 'index'])
        ->middleware('permission:laporan.view')
        ->name('laporan.index');
    Route::get('laporan/export', [LaporanController::class, 'export'])
        ->middleware('permission:laporan.view')
        ->name('laporan.export');

    // Kegiatan Penyuluhan
    Route::resource('kegiatan-penyuluhan', KegiatanPenyuluhanController::class)
        ->parameters(['kegiatan-penyuluhan' => 'kegiatanPenyuluhan'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:kegiatan-penyuluhan.view')
        ->middlewareFor(['create', 'store'], 'permission:kegiatan-penyuluhan.create')
        ->middlewareFor(['edit', 'update'], 'permission:kegiatan-penyuluhan.edit')
        ->middlewareFor('destroy', 'permission:kegiatan-penyuluhan.delete');
});

require __DIR__.'/settings.php';
