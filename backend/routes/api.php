<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{id}/add-member', [ProjectController::class, 'addMember']);
    
    Route::apiResource('tasks', TaskController::class);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/projects/{projectId}/users', [UserController::class, 'getTeamMembers']);
});