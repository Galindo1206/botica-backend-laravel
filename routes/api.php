<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Mail;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('suppliers', SupplierController::class);



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,5');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::apiResource('products', ProductController::class);
    Route::get('/roles', [UserController::class, 'roles']);
    Route::patch('/users/{user}/recover', [UserController::class, 'recover']);
    Route::delete('/users/{user}/force', [UserController::class, 'forceDestroy']);
    Route::apiResource('users', UserController::class);
});

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/test-email', function () {
    Mail::raw('Correo de prueba desde Laravel', function ($message) {
        $message->to('dstryergemura@gmail.com')
            ->subject('Prueba Laravel');
    });

    return response()->json([
        'message' => 'Correo enviado correctamente'
    ]);
});
Route::post(
    '/send-verification-code',
    [AuthController::class, 'sendVerificationCode']
);

Route::post(
    '/verify-email',
    [AuthController::class, 'verifyEmail']
);
