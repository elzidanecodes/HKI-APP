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

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Access control for legal documents (TECHNICAL_AUDIT.md C5): 'auth'
// establishes who the user is; DocumentController's own
// $this->authorize('view', ...) calls (Milestone M3.4) decide whether
// that specific user may see this specific document, per the approved
// authorization matrix. Authentication alone stopped being sufficient
// once M3.4 landed — a real, unauthenticated-URL exposure (Phase 0 /
// Milestone M0.3) is now fully closed.
Route::middleware('auth')->group(function () {
    Route::get('/documents/sio/{sios}', [DocumentController::class, 'sio'])->name('documents.sio.show');
    Route::get('/documents/silo/{silos}', [DocumentController::class, 'silo'])->name('documents.silo.show');
});

Route::get('/', function () {
    return redirect('/admin');
});
