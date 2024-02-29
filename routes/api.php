<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\DoctorAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\CustomerDashboardController;

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
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/customerRegister', [CustomerAuthController::class, 'register']);
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/doctorRegister', [DoctorAuthController::class, 'register']);
});

Route::middleware(['auth:api', 'role:doctor'])->group(function () {
   Route::get('/admin/admindashboard', [DoctorDashboardController::class, 'index']);
});


Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::get('/user/dashboard', [CustomerDashboardController::class, 'index']);
});



