<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('event-records', [EventController::class, 'index']);
Route::post('events', [EventController::class, 'store']);
Route::get('events/{event}', [EventController::class, 'show']);
Route::put('events/{event}', [EventController::class, 'update']);
Route::patch('events/{event}', [EventController::class, 'update']);
Route::delete('events/{event}', [EventController::class, 'destroy']);

Route::get('users', [UserManagementController::class, 'index']);
Route::post('users', [UserManagementController::class, 'store']);
Route::get('users/{user}', [UserManagementController::class, 'edit']);
Route::put('users/{user}', [UserManagementController::class, 'update']);
Route::patch('users/{user}', [UserManagementController::class, 'update']);
Route::delete('users/{user}', [UserManagementController::class, 'destroy']);

Route::get('students', [StudentController::class, 'index']);
Route::post('students', [StudentController::class, 'store']);
Route::get('students/{student}', [StudentController::class, 'show']);
Route::put('students/{student}', [StudentController::class, 'update']);
Route::patch('students/{student}', [StudentController::class, 'update']);
Route::delete('students/{student}', [StudentController::class, 'destroy']);

Route::get('reports', [ReportController::class, 'index']);
Route::get('reports/export', [ReportController::class, 'export']);
Route::get('reports/export/pdf', [ReportController::class, 'exportPdf']);

Route::get('my-registrations', [EventRegistrationController::class, 'index']);
Route::post('events/{event}/register', [EventRegistrationController::class, 'store']);
Route::patch('registrations/{registration}/cancel', [EventRegistrationController::class, 'destroy']);
Route::delete('registrations/{registration}', [EventRegistrationController::class, 'delete']);
