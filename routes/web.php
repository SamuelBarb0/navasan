<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenProduccionController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InsumoOrdenController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\ImpresionController;
use App\Http\Controllers\AcabadoController;
use App\Http\Controllers\RevisionController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/ordenes/create', [OrdenProduccionController::class, 'create'])->name('ordenes.create');
    Route::post('/ordenes', [OrdenProduccionController::class, 'store'])->name('ordenes.store');
    Route::get('/ordenes', [OrdenProduccionController::class, 'index'])->name('ordenes.index');
    Route::get('/ordenes/{orden}', [OrdenProduccionController::class, 'show'])->name('ordenes.show');
    Route::patch('/orden-etapas/{etapa}/iniciar', [\App\Http\Controllers\OrdenEtapaController::class, 'iniciar'])->name('orden_etapas.iniciar');
    Route::patch('/orden-etapas/{etapa}/finalizar', [\App\Http\Controllers\OrdenEtapaController::class, 'finalizar'])->name('orden_etapas.finalizar');

    Route::get('productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('productos/create', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('productos', [ProductoController::class, 'store'])->name('productos.store');

    Route::post('/clientes/ajax-store', [ClienteController::class, 'ajaxStore'])->name('clientes.ajaxStore');
    Route::resource('clientes', ClienteController::class)->except(['show']);
});

Route::patch('/insumos-orden/{id}/estado', [InsumoOrdenController::class, 'actualizarEstado'])
    ->name('insumo_orden.actualizar_estado')
    ->middleware('auth');

Route::post('/ordenes/{orden}/insumos', [InsumoOrdenController::class, 'store'])->name('ordenes.insumos.agregar');
Route::post('/insumos/crear-desde-orden', [InsumoOrdenController::class, 'storeDesdeOrden'])->name('insumos.store.desdeOrden');
Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
Route::put('/insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update');
Route::post('/insumos/recepcion', [InsumoController::class, 'storeRecepcion'])->name('insumos.recepcion.store');

Route::get('/impresiones', [ImpresionController::class, 'index'])->name('impresiones.index');
Route::post('/impresiones', [ImpresionController::class, 'store'])->name('impresiones.store');
Route::put('/impresiones/{id}', [ImpresionController::class, 'update'])->name('impresiones.update');

Route::get('/acabados', [AcabadoController::class, 'index'])->name('acabados.index');
Route::post('/acabados', [AcabadoController::class, 'store'])->name('acabados.store');

Route::get('/', [RevisionController::class, 'index'])->name('revisiones.index');
Route::post('/store', [RevisionController::class, 'store'])->name('revisiones.store');
require __DIR__ . '/auth.php';
