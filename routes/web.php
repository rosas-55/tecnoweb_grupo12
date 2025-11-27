<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Usuarios
    Route::resource('usuarios', UsuarioController::class)->except(['show']);
    
    // Insumos
    Route::resource('insumos', InsumoController::class)->except(['show']);
    
    // Recetas
    Route::resource('recetas', RecetaController::class);
    
    // Inventario (crear, editar, eliminar)
    Route::get('inventarios', [InventarioController::class, 'index'])->name('inventarios.index');
    Route::get('inventarios/create', [InventarioController::class, 'create'])->name('inventarios.create');
    Route::post('inventarios', [InventarioController::class, 'store'])->name('inventarios.store');
    Route::get('inventarios/{inventario}/edit', [InventarioController::class, 'edit'])->name('inventarios.edit');
    Route::put('inventarios/{inventario}', [InventarioController::class, 'update'])->name('inventarios.update');
    Route::delete('inventarios/{inventario}', [InventarioController::class, 'destroy'])->name('inventarios.destroy');
    Route::get('inventarios/kardex/{insumo}', [InventarioController::class, 'kardex'])->name('inventarios.kardex');
    
    // Producción (crear, editar, eliminar)
    Route::get('produccion', [ProduccionController::class, 'index'])->name('produccion.index');
    Route::get('produccion/create', [ProduccionController::class, 'create'])->name('produccion.create');
    Route::post('produccion', [ProduccionController::class, 'store'])->name('produccion.store');
    Route::get('produccion/{produccion}', [ProduccionController::class, 'show'])->name('produccion.show');
    Route::get('produccion/{produccion}/edit', [ProduccionController::class, 'edit'])->name('produccion.edit');
    Route::put('produccion/{produccion}', [ProduccionController::class, 'update'])->name('produccion.update');
    Route::delete('produccion/{produccion}', [ProduccionController::class, 'destroy'])->name('produccion.destroy');
    
    // Ventas (crear, editar, eliminar)
    Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::get('ventas/{venta}/edit', [VentaController::class, 'edit'])->name('ventas.edit');
    Route::put('ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update');
    Route::delete('ventas/{venta}', [VentaController::class, 'destroy'])->name('ventas.destroy');
    
    // Pagos (solo crear y eliminar)
    Route::get('pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('pagos/create', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('pagos/{pago}', [PagoController::class, 'show'])->name('pagos.show');
    Route::get('pagos/{pago}/check-status', [PagoController::class, 'checkStatus'])->name('pagos.check-status');
    Route::delete('pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
    
    // Reportes (solo consultar y generar)
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/generar', [ReporteController::class, 'generar'])->name('reportes.generar');
    Route::get('reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('reportes/produccion', [ReporteController::class, 'produccion'])->name('reportes.produccion');
});

// Callback de PagoFácil (sin autenticación)
Route::post('pagos/callback', [PagoController::class, 'callback'])->name('pagos.callback');
