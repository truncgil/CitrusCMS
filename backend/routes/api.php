<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\CategoryController;

// Modeller
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Basit sağlık kontrolü
Route::get('/health', fn() => response()->json(['ok' => true]));

// === Auth ===

// Giriş
Route::post('/auth/login', function (Request $request) {
    $data = $request->validate([
        'email'    => ['required','email'],
        'password' => ['required','string'],
    ]);

    if (!Auth::attempt($data)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /** @var \App\Models\User $user */
    $user  = Auth::user();
    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ]);
});

// Kimim?
Route::middleware('auth:sanctum')->get('/auth/user', fn(Request $r) => $r->user());

// Çıkış (aktif token'ı sil)
Route::middleware('auth:sanctum')->post('/auth/logout', function (Request $r) {
    $r->user()->currentAccessToken()->delete();
    return response()->json(['ok' => true]);
});

// === Protected API ===
// Buradaki tüm rotalar için Bearer token gerekir.
Route::middleware('auth:sanctum')->group(function () {
    // Pages CRUD
    Route::apiResource('pages', PageController::class);
    
    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);
});