<?php
use App\Http\Middleware\VerificarTokenInterno;
use App\Http\Controllers\Api\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/interno/productos/{producto}', [ProductoController::class, 'show'])
    ->middleware(VerificarTokenInterno::class);
Route::apiResource('productos', ProductoController::class);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
