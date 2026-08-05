<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DocumentController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('/auth/login');
});



Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Interim access control for legal documents (Phase 0 / Milestone M0.3):
// authentication only. Policy-scoped authorization lands in Phase 3.
Route::middleware('auth')->group(function () {
    Route::get('/documents/sio/{sios}', [DocumentController::class, 'sio'])->name('documents.sio.show');
    Route::get('/documents/silo/{silos}', [DocumentController::class, 'silo'])->name('documents.silo.show');
});

Route::get('/', function () {
    return redirect('/admin');
});
