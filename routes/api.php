<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentReminderController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\UserController;
use App\Models\Appointment;
use App\Models\Availability;

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
    Route::post('/doctor/availability', [AvailabilityController::class, 'availabilityStore']);
    Route::get('/doctor/available/list', [AvailabilityController::class, 'availableList']);
    Route::delete('/doctor/available/delete/{id}', [AvailabilityController::class, 'availableDelete']);
    Route::get('/doctor/appointment/clientList', [AppointmentController::class, 'clientList']);//list where customer already made an appointment
    Route::put('/doctor/appointment/clientList/cancel/{id}', [DoctorController::class, 'statusCancel']);
    Route::put('/doctor/appointment/clientList/completed/{id}', [DoctorController::class, 'statusComplete']);
    Route::get('/doctor/appointment/clientList/calendar', [DoctorController::class, 'doctorCalendar']);
});

Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::get('/user/dashboard', [CustomerController::class, 'dashboard']);
    Route::get('/user/appointment/availability', [AvailabilityController::class, 'doctorAvailableList']);
    Route::get('/user/appointment/availability/doctor/{id}', [AvailabilityController::class, 'doctorBookingProfile']);
    Route::post('/user/appointment', [AppointmentController::class, 'appointmentStore']);
    Route::get('/user/appointment/list', [AppointmentController::class, 'appointmentList']);
    Route::get('/user/appointment/clientList/calendar', [CustomerController::class, 'customerCalendar']);

});

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/dashboard/userList', [UserController::class, 'userList']);
    Route::put('/admin/doctorStatus/accept/{id}', [UserController::class, 'statusAccept']);
    Route::put('/admin/doctorStatus/reject/{id}', [UserController::class, 'statusReject']);
    Route::put('/admin/userList/edit/{id}', [UserController::class, 'userListEdit']);
});

Route::middleware(['auth:api'])->post('/appointment/email/notification', [AppointmentReminderController::class, 'emailNotification']);
Route::middleware(['auth:api'])->post('/update/notification/settings', [AppointmentReminderController::class, 'updateNotificationSettings']);