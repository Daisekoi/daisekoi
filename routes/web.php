<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});


use App\Http\Controllers\UserController;
// Route::post('/api/usersLogin', [UserController::class, 'login']);

// Route::get('/api/getUsersFile', [UserController::class, 'getUsers']); 
// Route::delete('/api/deleteUser', [UserController::class, 'deleteUser']);
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::put('/api/updateUser', [UserController::class, 'updateUser']);
Route::delete('/api/usersDelete', [UserController::class, 'deleteUser']);
Route::middleware(['web'])->group(function () {
    
    Route::get('/api/getUsersFile', [UserController::class, 'getUsers']);
    Route::post('/api/usersLogin', [UserController::class, 'login']);
});
