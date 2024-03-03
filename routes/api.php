<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/customerRegister', [AuthController::class, 'customerRegister']);
    Route::post('/doctorRegister', [AuthController::class, 'doctorRegister']);
});

Route::middleware(['auth:api', 'role:doctor'])->group(function () {
    Route::get('/doctor/dashboard', [DoctorController::class, 'dashboard']);
    Route::post('/doctor/availability', [DoctorController::class, 'availability']);
    Route::get('/doctor/availableList', [DoctorController::class, 'availableList']);
});

Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::get('/user/dashboard', [CustomerController::class, 'dashboard']);
});

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
