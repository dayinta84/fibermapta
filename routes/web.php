<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MapDataController;
use App\Http\Controllers\TitikJaringanController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\SimulasiTopologiPlbController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\UserController;

Route::get('/', fn () => redirect('/administrator'));

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/administrator', [AuthController::class, 'showLogin'])
    ->name('login')
    ->middleware('guest');

Route::post('/administrator', [AuthController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| SEMUA USER YANG LOGIN
| Role: superadmin, admin, teknisi
|--------------------------------------------------------------------------
| Bagian ini boleh diakses semua user login.
| Teknisi hanya boleh melihat data, bukan mengubah data utama.
*/

Route::middleware(['auth', 'role:superadmin,admin,teknisi'])->group(function () {
    Route::get('/dashboard', function () {
        return view('administrator.dashboard');
    })->name('dashboard');

    Route::get('/map-data', [MapDataController::class, 'index'])
        ->name('map.data');

// Lihat data titik jaringan
    Route::get('/titik-jaringan', [TitikJaringanController::class, 'index'])
        ->name('titik.index');

    Route::get('/titik-jaringan/{titik}/detail-odc', [TitikJaringanController::class, 'showOdc'])
        ->name('titik.detail_odc');

    Route::get('/titik-jaringan/{titik}/detail-odp', [TitikJaringanController::class, 'showOdp'])
        ->name('titik.detail_odp');

    Route::put('/ports/{port}/redaman', [PortController::class, 'updateRedaman'])
        ->name('ports.update_redaman');

// Lihat data pelanggan
    Route::get('/pelanggan', [PelangganController::class, 'index'])
        ->name('pelanggan.index');

// Lihat simulasi topologi PLB
    Route::get('/simulasi-topologi-plb', [SimulasiTopologiPlbController::class, 'index'])
        ->name('simulasi-topologi-plb.index');

    Route::get('/simulasi-topologi-plb/{simulasi}', [SimulasiTopologiPlbController::class, 'show'])
        ->name('simulasi-topologi-plb.show');

    // Teknisi boleh input redaman aktual port jika fitur ini digunakan untuk verifikasi lapangan.
    // Route::put('/ports/{port}/redaman', [PortController::class, 'updateRedaman'])
    //     ->name('ports.update_redaman');
});


/*
|--------------------------------------------------------------------------
| ADMIN DAN SUPERADMIN
|--------------------------------------------------------------------------
| Role: superadmin, admin
|--------------------------------------------------------------------------
| Bagian ini untuk aksi pengelolaan data.
| Teknisi tidak boleh masuk ke route-route ini.
*/

Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | CRUD Titik Jaringan
    |--------------------------------------------------------------------------
    */
    Route::post('/titik-jaringan', [TitikJaringanController::class, 'store'])
        ->name('titik.store');

    Route::put('/titik-jaringan/{titik}', [TitikJaringanController::class, 'update'])
        ->name('titik.update');

    Route::delete('/titik-jaringan/{titik}', [TitikJaringanController::class, 'destroy'])
        ->name('titik.destroy');

    // Route::post('/titik-jaringan/{titik}/generate-ports', [PortController::class, 'generate'])->name('ports.generate');


    /*
    |--------------------------------------------------------------------------
    | CRUD Pelanggan
    |--------------------------------------------------------------------------
    */
    Route::post('/pelanggan', [PelangganController::class, 'store'])
        ->name('pelanggan.store');

    Route::put('/pelanggan/{pelanggan}', [PelangganController::class, 'update'])
        ->name('pelanggan.update');

    Route::delete('/pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])
        ->name('pelanggan.destroy');


    /*
    |--------------------------------------------------------------------------
    | Simulasi Topologi PLB
    |--------------------------------------------------------------------------
    */
    Route::post('/simulasi-topologi-plb', [SimulasiTopologiPlbController::class, 'store'])
        ->name('simulasi-topologi-plb.store');

    Route::put('/simulasi-topologi-plb/{simulasi}/settings', [SimulasiTopologiPlbController::class, 'updateSettings'])
        ->name('simulasi-topologi-plb.update-settings');

    Route::post('/simulasi-topologi-plb/{simulasi}/topology', [SimulasiTopologiPlbController::class, 'updateTopology'])
        ->name('simulasi-topologi-plb.update-topology');

    Route::post('/simulasi-topologi-plb/{simulasi}/hitung', [SimulasiTopologiPlbController::class, 'hitung'])
        ->name('simulasi-topologi-plb.hitung');

    Route::delete('/simulasi-topologi-plb/{simulasi}', [SimulasiTopologiPlbController::class, 'destroy'])
        ->name('simulasi-topologi-plb.destroy');

});


/*
|--------------------------------------------------------------------------
| SUPERADMIN SAJA
|--------------------------------------------------------------------------
| Role: superadmin
|--------------------------------------------------------------------------
| Hanya superadmin yang boleh mengelola user.
*/
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');
});