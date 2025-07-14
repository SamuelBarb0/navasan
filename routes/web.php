<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    OrdenProduccionController,
    ClienteController,
    ProductoController,
    InsumoOrdenController,
    InsumoController,
    ImpresionController,
    AcabadoController,
    RevisionController,
    FacturacionController,
    InventarioEtiquetaController,
    DevolucionController,
    ReporteRevisadoController,
    OrdenEtapaController,
    UserController
};

// Página de bienvenida
Route::get('/', function () {
    return redirect()->route('ordenes.index');
});

// Dashboard general
Route::get('/dashboard', function () {
    return redirect()->route('ordenes.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Perfil de usuario
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =========================
// ORDENES DE PRODUCCIÓN
// =========================
Route::middleware(['auth', 'role:preprensa|administrador'])->group(function () {
    Route::get('/ordenes/create', [OrdenProduccionController::class, 'create'])->name('ordenes.create');
    Route::post('/ordenes', [OrdenProduccionController::class, 'store'])->name('ordenes.store');
    Route::delete('/ordenes/{orden}', [OrdenProduccionController::class, 'destroy'])->name('ordenes.destroy'); // 👈 Ruta añadida
    Route::delete('/ordenes/insumos/{id}/eliminar', [InsumoOrdenController::class, 'destroy'])->name('ordenes.insumos.eliminar');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/ordenes', [OrdenProduccionController::class, 'index'])->name('ordenes.index');
    Route::get('/ordenes/{orden}', [OrdenProduccionController::class, 'show'])->name('ordenes.show');
    Route::get('/ordenes/{orden}/productos-json', [OrdenProduccionController::class, 'productosDeOrden']);
    Route::get('/ordenes/{orden}/items-json', [OrdenProduccionController::class, 'itemsJson']);
});

Route::middleware(['auth'])->group(function () {
    Route::patch('/orden-etapas/{etapa}/iniciar', [OrdenEtapaController::class, 'iniciar'])->name('orden_etapas.iniciar');
    Route::patch('/orden-etapas/{etapa}/finalizar', [OrdenEtapaController::class, 'finalizar'])->name('orden_etapas.finalizar');
});

// =========================
// PRODUCTOS Y CLIENTES
// =========================
Route::middleware(['auth', 'role:preprensa|administrador'])->group(function () {
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::post('/clientes/ajax-store', [ClienteController::class, 'ajaxStore'])->name('clientes.ajaxStore');

    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    Route::get('/productos-por-cliente/{clienteId}', [ProductoController::class, 'porCliente']);
});

Route::prefix('usuarios')->name('usuarios.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');         // Mostrar todos los usuarios
    Route::get('/create', [UserController::class, 'create'])->name('create'); // Formulario de creación
    Route::post('/', [UserController::class, 'store'])->name('store');        // Guardar nuevo usuario
    Route::get('/{usuario}', [UserController::class, 'show'])->name('show');  // Ver detalle de usuario
    Route::get('/{usuario}/edit', [UserController::class, 'edit'])->name('edit'); // Formulario de edición
    Route::put('/{usuario}', [UserController::class, 'update'])->name('update');  // Actualizar usuario
    Route::delete('/{usuario}', [UserController::class, 'destroy'])->name('destroy'); // Eliminar usuario
});

// =========================
// INSUMOS
// =========================
Route::middleware(['auth', 'role:almacen|administrador'])->group(function () {
    Route::patch('/insumos-orden/{id}/estado', [InsumoOrdenController::class, 'actualizarEstado'])->name('insumo_orden.actualizar_estado');
    Route::post('/ordenes/{orden}/insumos', [InsumoOrdenController::class, 'store'])->name('ordenes.insumos.agregar');
    Route::post('/insumos/crear-desde-orden', [InsumoOrdenController::class, 'storeDesdeOrden'])->name('insumos.store.desdeOrden');

    Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
    Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
    Route::put('/insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update');
    Route::post('/insumos/recepcion', [InsumoController::class, 'storeRecepcion'])->name('insumos.recepcion.store');
});

// =========================
// IMPRESIÓN
// =========================
Route::middleware(['auth', 'role:impresion|preprensa|administrador'])->group(function () {
    Route::get('/impresiones', [ImpresionController::class, 'index'])->name('impresiones.index');
    Route::post('/impresiones', [ImpresionController::class, 'store'])->name('impresiones.store');
    Route::put('/impresiones/{id}', [ImpresionController::class, 'update'])->name('impresiones.update');
});

// =========================
// ACABADOS
// =========================
Route::middleware(['auth', 'role:acabados|administrador'])->group(function () {
    Route::get('/acabados', [AcabadoController::class, 'index'])->name('acabados.index');
    Route::post('/acabados', [AcabadoController::class, 'store'])->name('acabados.store');
    Route::put('/acabados/{id}', [AcabadoController::class, 'update'])->name('acabados.update');

});

// =========================
// REVISIÓN
// =========================
Route::middleware(['auth', 'role:revision|administrador'])->group(function () {
    Route::get('/revisiones', [RevisionController::class, 'index'])->name('revisiones.index');
    Route::post('/store', [RevisionController::class, 'store'])->name('revisiones.store');
});

// =========================
// FACTURACIÓN
// =========================
Route::middleware(['auth', 'role:logistica|administrador'])->group(function () {
    Route::get('/facturacion', [FacturacionController::class, 'index'])->name('facturacion.index');
    Route::post('/facturacion', [FacturacionController::class, 'store'])->name('facturacion.store');
    Route::get('/facturacion/{id}/descargar', [FacturacionController::class, 'descargarFactura'])->name('facturacion.descargar');
});

// =========================
// INVENTARIO DE ETIQUETAS
// =========================
Route::middleware(['auth', 'role:almacen|administrador'])->group(function () {
    Route::resource('inventario-etiquetas', InventarioEtiquetaController::class);
    Route::put('/inventario-etiquetas/{id}', [InventarioEtiquetaController::class, 'update'])->name('inventario-etiquetas.update');
});

// =========================
// DEVOLUCIONES
// =========================
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('devoluciones', DevolucionController::class)->only(['index', 'store']);
});

// =========================
// REPORTE DE REVISADO
// =========================
Route::middleware(['auth', 'role:preprensa|administrador'])->group(function () {
    Route::get('/reportes/revisado', [ReporteRevisadoController::class, 'index'])->name('reportes.revisado');
});

require __DIR__ . '/auth.php';
