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
    UserController,
    CategoriaController,
    EtapaProduccionController
};
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

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
});

Route::get('/ordenes/{orden}/items-json', [OrdenProduccionController::class, 'itemsJson']);

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
});

Route::get('/productos-por-cliente/{clienteId}', [ProductoController::class, 'porCliente']);

Route::get('/productos/todos-json', function () {
    return \App\Models\Producto::select('id', 'nombre')->orderBy('nombre')->get();
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
    Route::delete('/insumos/{insumo}', [InsumoController::class, 'destroy'])->name('insumos.destroy');
});

// =========================
// IMPRESIÓN
// =========================
Route::middleware(['auth', 'role:impresion|preprensa|administrador'])->group(function () {
    Route::get('/impresiones', [ImpresionController::class, 'index'])->name('impresiones.index');
    Route::post('/impresiones', [ImpresionController::class, 'store'])->name('impresiones.store');
    Route::put('/impresiones/{id}', [ImpresionController::class, 'update'])->name('impresiones.update');
    Route::delete('/impresiones/{impresion}', [ImpresionController::class, 'destroy'])
        ->name('impresiones.destroy');
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
    Route::put('/revisiones/{id}', [RevisionController::class, 'update'])->name('revisiones.update');
    Route::delete('/revisiones/{id}', [RevisionController::class, 'destroy'])->name('revisiones.destroy');
    Route::post('/revisiones/{id}/alerta', [RevisionController::class, 'alerta'])->name('revisiones.alerta');
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
    Route::delete('/inventario-etiquetas/{inventarioEtiqueta}', [InventarioEtiquetaController::class, 'destroy'])->name('inventario-etiquetas.destroy');
});

// =========================
// DEVOLUCIONES
// =========================
Route::middleware(['auth', 'role:devoluciones|administrador'])->group(function () {
    Route::resource('devoluciones', DevolucionController::class)->only(['index', 'store']);
    Route::get('/ordenes/{orden}/revisiones', [DevolucionController::class, 'revisionesPorOrden'])
        ->middleware('auth')
        ->name('ordenes.revisiones');
    Route::delete('/devoluciones/{devolucion}', [DevolucionController::class, 'destroy'])
        ->middleware('auth')
        ->name('devoluciones.destroy');
});

// =========================
// REPORTE DE REVISADO
// =========================
Route::middleware(['auth', 'role:preprensa|administrador'])->group(function () {
    Route::get('/reportes/revisado', [ReporteRevisadoController::class, 'index'])->name('reportes.revisado');
});
Route::resource('etapas', EtapaProduccionController::class);

Route::prefix('suaje-corte')->name('suaje-corte.')->group(function () {
    Route::get('/',        [AcabadoController::class, 'index'])->name('index');
    Route::post('/',       [AcabadoController::class, 'store'])->name('store');
    Route::put('/{id}',    [AcabadoController::class, 'update'])->name('update');
    Route::delete('/{id}', [AcabadoController::class, 'destroy'])->name('destroy'); // 👈 nuevo
});

Route::prefix('laminado')->name('laminado.')->group(function () {
    Route::get('/',        [AcabadoController::class, 'index'])->name('index');
    Route::post('/',       [AcabadoController::class, 'store'])->name('store');
    Route::put('/{id}',    [AcabadoController::class, 'update'])->name('update');
    Route::delete('/{id}', [AcabadoController::class, 'destroy'])->name('destroy'); // 👈 nuevo
});

Route::prefix('empalmado')->name('empalmado.')->group(function () {
    Route::get('/',        [AcabadoController::class, 'index'])->name('index');
    Route::post('/',       [AcabadoController::class, 'store'])->name('store');
    Route::put('/{id}',    [AcabadoController::class, 'update'])->name('update');
    Route::delete('/{id}', [AcabadoController::class, 'destroy'])->name('destroy'); // 👈 nuevo
});

// Setear aviso (global o desfase) para suaje | laminado | empalmado | impresion
Route::post('/toasts/{tipo}/{scope}/set', function (Request $r, string $tipo, string $scope) {
    $tipos  = ['suaje','laminado','empalmado','impresion']; // 👈 agregado impresion
    $scopes = ['global','desfase'];

    if (!in_array($tipo, $tipos, true) || !in_array($scope, $scopes, true)) {
        abort(404);
    }

    $porDefecto = $scope === 'desfase'
        ? "⚠ Desfase en " . ucfirst($tipo)
        : "⚠ Aviso global de " . $tipo;

    $msg = (string) $r->input('message', $porDefecto);

    $key = $scope === 'desfase'
        ? "toast_{$tipo}_desfase_global"
        : "toast_{$tipo}_global";

    Cache::forever($key, $msg);
    return back();
})->name('toasts.set');

Route::post('/toasts/{tipo}/{scope}/clear', function (string $tipo, string $scope) {
    $tipos  = ['suaje','laminado','empalmado','impresion']; // 👈 agregado impresion
    $scopes = ['global','desfase'];

    if (!in_array($tipo, $tipos, true) || !in_array($scope, $scopes, true)) {
        abort(404);
    }

    // Si no es admin, no tocar cache y solo recargar
    $user = request()->user();
    if (!$user || !$user->hasRole('administrador')) {
        return back();
    }

    $key = $scope === 'desfase'
        ? "toast_{$tipo}_desfase_global"
        : "toast_{$tipo}_global";

    \Illuminate\Support\Facades\Cache::forget($key);
    return back()->with('success', 'Alerta cerrada.');
})->name('toasts.clear');


Route::get('/ordenes/{orden}/items-json', [AcabadoController::class, 'productosPorOrden'])
    ->whereNumber('orden')
    ->name('ordenes.items_json');

Route::get('/clientes/{cliente}/productos-json', [ProductoController::class, 'inventarioPorCliente'])
    ->name('clientes.productos.json');

// routes/web.php
Route::get('/ordenes/{orden}/revisiones-json', [OrdenProduccionController::class, 'revisionesJson'])
    ->middleware('auth');


Route::resource('categorias', CategoriaController::class);

Route::post('/revisiones/limpiar-toast', function () {
    session()->forget('mostrar_toast_revision');
    Cache::forget('toast_revision_ordenes'); // por si lo usaste antes
    return response()->noContent();
})->name('revisiones.limpiar.toast')->middleware('web');


require __DIR__ . '/auth.php';
