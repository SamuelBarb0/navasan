<?php

namespace App\Http\Controllers;

use App\Models\InventarioEtiqueta;
use App\Models\OrdenProduccion;
use App\Models\ItemOrden;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InventarioEtiquetaController extends Controller
{
    /**
     * Directorio raíz público absoluto y subcarpeta relativa para imágenes.
     * Cambia estas constantes si mueves el hosting.
     */
    private const PUBLIC_ROOT_ABS = '/home/u646187213/domains/navasan.site/public_html';
    private const IMG_REL_DIR     = 'images/productos'; // relativa a PUBLIC_ROOT_ABS

    /**
     * Muestra el formulario y lista de inventario.
     */
    public function index()
    {
        $ordenes     = OrdenProduccion::orderBy('created_at', 'desc')->get();
        $inventarios = InventarioEtiqueta::with(['orden', 'itemOrden', 'producto'])->latest()->get();
        $productos   = Producto::orderBy('nombre')->get();
        $clientes    = \App\Models\Cliente::orderBy('nombre')->get();

        return view('inventario-etiquetas.index', compact('ordenes','inventarios','productos','clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'orden_id'         => 'nullable|exists:orden_produccions,id',
            'item_orden_id'    => 'nullable|required_with:orden_id|exists:item_ordens,id',
            'producto_id'      => 'nullable|required_without:orden_id|exists:productos,id',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'cantidad'         => 'required|integer|min:1',
            'fecha_programada' => 'nullable|date|after_or_equal:today',
            'observaciones'    => 'nullable|string|max:1000',
            'imagen'           => 'nullable|image|max:4096',
        ]);

        try {
            $etiqueta = new InventarioEtiqueta();
            $etiqueta->orden_id         = $request->orden_id ?: null;
            $etiqueta->item_orden_id    = $request->orden_id ? $request->item_orden_id : null;
            $etiqueta->producto_id      = $request->orden_id ? null : $request->producto_id;
            $etiqueta->cliente_id       = $request->cliente_id ?: null;
            $etiqueta->cantidad         = $request->cantidad;
            $etiqueta->fecha_programada = $request->fecha_programada;
            $etiqueta->observaciones    = $request->observaciones;
            $etiqueta->estado           = 'pendiente';
            $etiqueta->alertado         = false;

            // Subir imagen si viene
            if ($request->hasFile('imagen')) {
                $etiqueta->imagen_path = $this->saveUploadedImage($request->file('imagen'));
            }

            $etiqueta->save();

            return back()->with('success', 'Inventario de etiquetas registrado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al guardar etiqueta: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'No se pudo guardar la etiqueta. Intenta de nuevo.');
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit($id)
    {
        $inventario = InventarioEtiqueta::findOrFail($id);
        $ordenes    = OrdenProduccion::all();
        $productos  = Producto::all();

        return view('inventario-etiquetas.edit', compact('inventario', 'ordenes', 'productos'));
    }

    public function update(Request $request, $id)
    {
        Log::info('[InventarioEtiqueta.update] Inicio', [
            'etiqueta_id' => $id,
            'user_id'     => auth()->id(),
            'roles'       => auth()->user()?->getRoleNames()?->toArray(),
        ]);

        if (!auth()->user()->hasAnyRole(['almacen', 'administrador'])) {
            Log::warning('[InventarioEtiqueta.update] Permiso denegado', ['user_id' => auth()->id()]);
            return back()->with('error', 'No tienes permiso para actualizar etiquetas.');
        }

        // Log del request antes de validar
        Log::debug('[InventarioEtiqueta.update] Payload recibido', [
            'all'         => $request->except(['imagen']),
            'has_imagen'  => $request->hasFile('imagen'),
            'imagen_size' => $request->file('imagen')?->getSize(),
            'mime'        => $request->file('imagen')?->getMimeType(),
        ]);

        $validator = Validator::make($request->all(), [
            'orden_id'         => 'nullable|exists:orden_produccions,id',
            'item_orden_id'    => 'nullable|required_with:orden_id|exists:item_ordens,id',
            'producto_id'      => 'nullable|required_without:orden_id|exists:productos,id',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'cantidad'         => 'required|numeric|min:1',
            'fecha_programada' => 'nullable|date',
            'observaciones'    => 'nullable|string|max:1000',
            'estado'           => 'required|in:pendiente,liberado,stock',
            'imagen'           => 'nullable|image|max:4096',
            'eliminar_imagen'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('[InventarioEtiqueta.update] Validación FALLÓ', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Revisa los campos resaltados. (Detalle en logs)');
        }

        try {
            $etiqueta = InventarioEtiqueta::findOrFail($id);

            // Rama (orden vs producto)
            $usaOrden = (bool) $request->filled('orden_id');
            Log::debug('[InventarioEtiqueta.update] Rama seleccionada', [
                'usa_orden'     => $usaOrden,
                'orden_id'      => $request->orden_id,
                'item_orden_id' => $request->item_orden_id,
                'producto_id'   => $request->producto_id,
            ]);

            // Asignaciones principales
            $etiqueta->orden_id         = $usaOrden ? $request->orden_id : null;
            $etiqueta->item_orden_id    = $usaOrden ? $request->item_orden_id : null;
            $etiqueta->producto_id      = $usaOrden ? null : $request->producto_id;
            $etiqueta->cliente_id       = $request->cliente_id ?: null;
            $etiqueta->cantidad         = $request->cantidad;
            $etiqueta->fecha_programada = $request->fecha_programada;
            $etiqueta->observaciones    = $request->observaciones;
            $etiqueta->estado           = $request->estado;

            // Eliminar imagen actual si lo piden
            if ($request->boolean('eliminar_imagen') && $etiqueta->imagen_path) {
                $this->deleteImageIfExists($etiqueta->imagen_path);
                Log::info('[InventarioEtiqueta.update] Imagen eliminada por checkbox', [
                    'path' => $etiqueta->imagen_path,
                ]);
                $etiqueta->imagen_path = null;
            }

            // Reemplazar imagen si suben nueva
            if ($request->hasFile('imagen')) {
                if ($etiqueta->imagen_path) {
                    $this->deleteImageIfExists($etiqueta->imagen_path);
                    Log::info('[InventarioEtiqueta.update] Reemplazando imagen previa', [
                        'prev_path' => $etiqueta->imagen_path,
                    ]);
                }
                $etiqueta->imagen_path = $this->saveUploadedImage($request->file('imagen'));
                Log::info('[InventarioEtiqueta.update] Nueva imagen subida', [
                    'new_path' => $etiqueta->imagen_path,
                ]);
            }

            $saved = $etiqueta->save();
            Log::info('[InventarioEtiqueta.update] Guardado OK', [
                'saved'       => $saved,
                'id'          => $etiqueta->id,
                'imagen_path' => $etiqueta->imagen_path,
            ]);

            return redirect()->route('inventario-etiquetas.index')->with('success', 'Etiqueta actualizada correctamente.');
        } catch (\Throwable $e) {
            Log::error('[InventarioEtiqueta.update] EXCEPCIÓN', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'No se pudo actualizar la etiqueta. Revisa el log.');
        }
    }

    /**
     * Elimina el registro de inventario.
     */
    public function destroy($id)
    {
        $inventario = InventarioEtiqueta::findOrFail($id);

        // Borra la imagen física si existe
        if ($inventario->imagen_path) {
            $this->deleteImageIfExists($inventario->imagen_path);
        }

        $inventario->delete();

        return redirect()->back()->with('success', 'Inventario eliminado correctamente.');
    }

    /**
     * Guarda un archivo subido en el directorio configurado y retorna la ruta relativa web.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string relative path (e.g., "images/productos/archivo.jpg")
     */
    private function saveUploadedImage($file): string
    {
        // Asegura carpeta destino
        $destAbs = rtrim(self::PUBLIC_ROOT_ABS, '/') . '/' . trim(self::IMG_REL_DIR, '/');
        if (!is_dir($destAbs)) {
            @mkdir($destAbs, 0755, true);
        }

        // Nombre único y "limpio"
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext          = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $safeName     = Str::slug(Str::limit($originalName, 80, '')) ?: 'img';
        $filename     = time() . '_' . uniqid() . '_' . $safeName . '.' . $ext;

        // Mover archivo
        $file->move($destAbs, $filename);

        // Retorna ruta relativa accesible por web
        return trim(self::IMG_REL_DIR, '/') . '/' . $filename;
    }

    /**
     * Elimina una imagen física si existe. Recibe ruta relativa (e.g. "images/productos/archivo.jpg").
     */
    private function deleteImageIfExists(?string $relativePath): void
    {
        if (!$relativePath) return;
        $absPath = rtrim(self::PUBLIC_ROOT_ABS, '/') . '/' . ltrim($relativePath, '/');
        if (is_file($absPath) && file_exists($absPath)) {
            @unlink($absPath);
        }
    }
}
