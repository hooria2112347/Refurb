<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});


Route::post('/signup', [AuthController::class, 'register'])->middleware('api');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


