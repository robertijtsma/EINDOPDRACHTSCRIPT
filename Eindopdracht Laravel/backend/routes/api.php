<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NoteController;

// Publieke deuren
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Beveiligde deuren
Route::middleware('auth:sanctum')->group(function () {
    
    // User & Auth
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Categories & Tickets CRUD
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tickets', TicketController::class);

    // Reacties & Notities
    Route::get('/tickets/{ticket}/comments', [CommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store']);
    
    Route::get('/tickets/{ticket}/notes', [NoteController::class, 'index']);
    Route::post('/tickets/{ticket}/notes', [NoteController::class, 'store']);
    
});