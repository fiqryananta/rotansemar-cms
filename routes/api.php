<?php

use App\Http\Controllers\Api\Mobile\V1\AuthController;
use App\Http\Controllers\Api\Mobile\V1\DashboardController;
use App\Http\Controllers\Api\Mobile\V1\FaskesController;
use App\Http\Controllers\Api\Mobile\V1\KegiatanPenyuluhanController;
use App\Http\Controllers\Api\Mobile\V1\KebutuhanController;
use App\Http\Controllers\Api\Mobile\V1\WilkerController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

        Route::get('/faskes/patients', [FaskesController::class, 'patients']);
        Route::get('/faskes/patients/{pasien}', [FaskesController::class, 'show']);
        Route::get('/faskes/medication-pickups', [FaskesController::class, 'medicationPickups']);
        Route::patch('/faskes/medication-pickups/{pickup}/status', [FaskesController::class, 'updateMedicationPickupStatus']);
        Route::get('/faskes/visit-results', [FaskesController::class, 'visitResults']);
        Route::post('/faskes/visit-results', [FaskesController::class, 'storeVisitResult']);

        Route::get('/wilker/patients', [WilkerController::class, 'patients']);
        Route::get('/wilker/patients/{pasien}', [WilkerController::class, 'show']);
        Route::get('/kegiatan-penyuluhan', [KegiatanPenyuluhanController::class, 'index']);
        Route::get('/kegiatan-penyuluhan/{kegiatanPenyuluhan}', [KegiatanPenyuluhanController::class, 'show']);
        Route::post('/kegiatan-penyuluhan', [KegiatanPenyuluhanController::class, 'store']);
        Route::post('/kegiatan-penyuluhan/{kegiatanPenyuluhan}', [KegiatanPenyuluhanController::class, 'update']);
        Route::delete('/kegiatan-penyuluhan/{kegiatanPenyuluhan}', [KegiatanPenyuluhanController::class, 'destroy']);

        Route::get('/kebutuhans/{pasienKebutuhan}', [KebutuhanController::class, 'show']);
        Route::patch('/kebutuhans/{pasienKebutuhan}/verify', [KebutuhanController::class, 'verify']);
        Route::post('/kebutuhans/{pasienKebutuhan}/follow-ups', [KebutuhanController::class, 'addFollowUp']);
        Route::patch('/kebutuhans/{pasienKebutuhan}/mark-done', [KebutuhanController::class, 'markDone']);
    });
});
