<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomRequestController;

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

// Catch-all route for SPA
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*'); // Exclude paths starting with "api" 
Route::middleware(['auth'])->group(function () {
    Route::get('/custom-request/create', [CustomRequestController::class, 'create'])->name('custom-request.create');
    Route::post('/custom-request/store', [CustomRequestController::class, 'store'])->name('custom-request.store');
});