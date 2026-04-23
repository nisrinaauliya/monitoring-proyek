<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PpsmbByUserController;
use App\Http\Controllers\PpsmbByCmdController;
use App\Http\Controllers\PpsmbByDinovController;
use App\Http\Controllers\PpsmbByItController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InputPriorityController;
use App\Http\Controllers\ResultController;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/ppsmbbyuser', [PpsmbByUserController::class, 'index'])
        ->name('ppsmbbyuser');
    Route::get('/ppsmbbyuser/create', [PpsmbByUserController::class, 'create'])
        ->name('ppsmbbyuser.create');
    Route::post('/ppsmbbyuser', [PpsmbByUserController::class, 'store'])
        ->name('ppsmbbyuser.store');
    Route::get('/ppsmbbyuser/checkkuat', [PpsmbByUserController::class, 'checkUat'])
        ->name('ppsmbbyuser.checkuat');    
    Route::get('/ppsmbbyuser/{id}', [PpsmbByUserController::class, 'show'])
        ->name('ppsmbbyuser.show');
    Route::get('/ppsmbbyuser/{id}/edit', [PpsmbByUserController::class, 'edit'])
        ->name('ppsmbbyuser.edit');
    Route::put('/ppsmbbyuser/{id}', [PpsmbByUserController::class, 'update'])
        ->name('ppsmbbyuser.update');

    Route::middleware(['auth', 'checkdept:CMD'])->group(function () {
        Route::get('/ppsmbbycmd', [PpsmbByCmdController::class, 'index'])
            ->name('ppsmbbycmd');
        Route::get('/ppsmbbycmd/{id}', [PpsmbByCmdController::class, 'show'])
            ->name('ppsmbbycmd.show');
        Route::post('/ppsmbbycmd/{id}/approve', [PpsmbByCmdController::class, 'approve'])
            ->name('ppsmbbycmd.approve');
        Route::post('/ppsmbbycmd/{id}/revisi', [PpsmbByCmdController::class, 'revisi'])
            ->name('ppsmbbycmd.revisi');
    });

    Route::middleware(['auth', 'checkdept:DINOV'])->group(function () {
        Route::get('/ppsmbbydinov', [PpsmbByDinovController::class, 'index'])
            ->name('ppsmbbydinov');
        Route::get('/ppsmbbydinov/{id}', [PpsmbByDinovController::class, 'show'])
            ->name('ppsmbbydinov.show');
        Route::post('/ppsmbbydinov/{id}/approve', [PpsmbByDinovController::class, 'approve'])
            ->name('ppsmbbydinov.approve');
        Route::post('/ppsmbbydinov/{id}/revisi', [PpsmbByDinovController::class, 'revisi'])
            ->name('ppsmbbydinov.revisi');
    });

    Route::middleware(['auth', 'checkdept:IT'])->group(function () {
        Route::get('/ppsmbbyit', [PpsmbByItController::class, 'index'])
            ->name('ppsmbbyit');
        Route::get('/ppsmbbyit/{id}', [PpsmbByItController::class, 'show'])
            ->name('ppsmbbyit.show');
        Route::post('/ppsmbbyit/{id}/generate', [PpsmbByItController::class, 'generateNoPpsmb'])
            ->name('ppsmbbyit.generate');
        Route::post('/ppsmbbyit/{id}/detail', [PpsmbByItController::class, 'simpanDetail'])
            ->name('ppsmbbyit.detail');
        Route::post('/ppsmbbyit/{id}/developer', [PpsmbByItController::class, 'assignDeveloper'])
            ->name('ppsmbbyit.developer');
        Route::post('/ppsmbbyit/{id}/estimasi', [PpsmbByItController::class, 'updateEstimasi'])
            ->name('ppsmbbyit.estimasi');
        Route::post('/ppsmbbyit/{id}/progress', [PpsmbByItController::class, 'updateProgress'])
            ->name('ppsmbbyit.progress');
        Route::post('/ppsmbbyit/{id}/lanjutuat', [PpsmbByItController::class, 'lanjutUat'])
            ->name('ppsmbbyit.lanjutuat');
        Route::post('/ppsmbbyit/{id}/uat', [PpsmbByItController::class, 'updateUat'])
            ->name('ppsmbbyit.uat');
    });

    Route::middleware(['auth', 'checkdept:CMD, DINOV, IT'])->group(function () {
        Route::get('/report', [ReportController::class, 'index'])
            ->name('report');

        Route::get('/result', [ResultController::class, 'index'])
            ->name('result');
    });
});

    Route::post('/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');