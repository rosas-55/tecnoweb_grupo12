<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
Route::put('/insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update');
Route::delete('/insumos/{insumo}', [InsumoController::class, 'destroy'])->name('insumos.destroy');

Route::get('/recetas', [RecetaController::class, 'index'])->name('recetas.index');
Route::post('/recetas', [RecetaController::class, 'store'])->name('recetas.store');
Route::put('/recetas/{receta}', [RecetaController::class, 'update'])->name('recetas.update');
Route::delete('/recetas/{receta}', [RecetaController::class, 'destroy'])->name('recetas.destroy');

Route::get('/produccion', [ProduccionController::class, 'index'])->name('produccion.index');
Route::post('/produccion', [ProduccionController::class, 'store'])->name('produccion.store');
Route::put('/produccion/{produccion}', [ProduccionController::class, 'update'])->name('produccion.update');
Route::delete('/produccion/{produccion}', [ProduccionController::class, 'destroy'])->name('produccion.destroy');

Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
Route::put('/ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update');
Route::delete('/ventas/{venta}', [VentaController::class, 'destroy'])->name('ventas.destroy');
Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
Route::put('/pagos/{pago}', [PagoController::class, 'update'])->name('pagos.update');
Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
Route::put('/inventario/{inventario}', [InventarioController::class, 'update'])->name('inventario.update');
Route::delete('/inventario/{inventario}', [InventarioController::class, 'destroy'])->name('inventario.destroy');
Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
// Reportes eliminado
