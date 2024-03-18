<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentReminderController;
use App\Models\Appointment;

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
    Route::post('/doctor/availability', [DoctorController::class, 'availabilityStore']);
    Route::get('/doctor/available/list', [DoctorController::class, 'availableList']);
    Route::delete('/doctor/available/delete/{id}', [DoctorController::class, 'availableDelete']);
    Route::get('/doctor/appointment/clientList', [DoctorController::class, 'clientList']);//list where customer already made an appointment
    Route::put('/doctor/appointment/clientList/accept/{id}',[DoctorController::class, 'statusAccept']);
    Route::put('/doctor/appointment/clientList/reject/{id}', [DoctorController::class, 'statusReject']);
    Route::put('/doctor/appointment/clientList/completed/{id}', [DoctorController::class, 'statusComplete']);
    Route::get('/doctor/appointment/clientList/calendar', [DoctorController::class, 'clientListCalendar']);
});

Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::get('/user/dashboard', [CustomerController::class, 'dashboard']);
    Route::get('/user/appointment/availability', [CustomerController::class, 'doctorAvailableList']);
    Route::get('/user/appointment/availability/doctor/{id}', [CustomerController::class, 'doctorBookingProfile']);
    Route::post('/user/appointment', [CustomerController::class, 'appointmentStore']);
    Route::get('/user/appointment/list', [CustomerController::class, 'appointmentList']);
    Route::get('/user/appointment/clientList/calendar', [CustomerController::class, 'customerCalendar']);

});


Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/dashboard/userList', [AdminController::class, 'userList']);
    Route::put('/admin/doctorStatus/accept/{id}', [AdminController::class, 'statusAccept']);
    Route::put('/admin/doctorStatus/reject/{id}', [AdminController::class, 'statusReject']);
});

Route::middleware(['auth:api'])->post('/appointment/email/notification', [AppointmentReminderController::class, 'emailNotification']);
Route::middleware(['auth:api'])->post('/update/notification/settings', [AppointmentReminderController::class, 'updateNotificationSettings']);