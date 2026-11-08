<?php
use App\Models\Producto;

use Illuminate\Support\Facades\Route;
Route::get('/inventario', fn () => view('inventario', ['productos' => Producto::all()]));

Route::get('/', function () {
    return view('welcome');
});
