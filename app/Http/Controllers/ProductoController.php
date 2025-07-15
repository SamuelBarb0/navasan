<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::orderBy('nombre')->get(); // 👈 importante
        $query = Producto::query()->orderBy('nombre');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $productos = $query->get();

        return view('productos.index', compact('productos', 'clientes'));
    }


    public function create()
    {
        return view('productos.create');
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'codigo' => 'required|string|unique:productos,codigo,' . $id,
            'nombre' => 'required|string',
            'presentacion' => 'nullable|string',
            'unidad' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'producto_cliente' => 'nullable|exists:clientes,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['cliente_id'] = $request->input('producto_cliente'); // 👈 Asignamos manualmente el cliente_id



        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = uniqid() . '.' . $imagen->getClientOriginalExtension();
            $rutaDestino = '/home/u646187213/domains/navasan.site/public_html/images/productos';

            if (!file_exists($rutaDestino)) {
                mkdir($rutaDestino, 0775, true);
            }

            $imagen->move($rutaDestino, $nombreImagen);
            $data['imagen'] = 'images/productos/' . $nombreImagen;
        }

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);

            // Si tiene imagen asociada, eliminarla del servidor
            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                unlink(public_path($producto->imagen));
            }

            $producto->delete();

            return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
        } catch (\Throwable $e) {
            \Log::error('❌ Error al eliminar producto: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el producto.');
        }
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|unique:productos,codigo',
            'nombre' => 'required|string',
            'presentacion' => 'nullable|string',
            'unidad' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'producto_cliente' => 'nullable|exists:clientes,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['cliente_id'] = $request->input('producto_cliente');

        try {
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = uniqid() . '.' . $imagen->getClientOriginalExtension();

                $rutaDestino = public_path('images/productos');
                if (!file_exists($rutaDestino)) {
                    mkdir($rutaDestino, 0775, true);
                }

                $imagen->move($rutaDestino, $nombreImagen);
                $data['imagen'] = 'images/productos/' . $nombreImagen;
            }

            $producto = Producto::create($data);

            // ✅ Verificamos si viene desde AJAX
            if ($request->ajax()) {
                return response()->json($producto); // ← para ordenes/create
            }

            // ✅ Si es desde productos, recarga con mensaje
            return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
        } catch (\Throwable $e) {
            \Log::error('❌ Error al guardar producto: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['message' => 'Error inesperado.'], 500);
            }

            return redirect()->back()->with('error', 'Ocurrió un error inesperado.');
        }
    }

    public function porCliente($clienteId)
    {
        Log::debug("🔍 Consultando productos para cliente ID: {$clienteId}");

        $productos = Producto::where('cliente_id', $clienteId)->get();

        if ($productos->isEmpty()) {
            Log::warning("⚠️ Cliente ID {$clienteId} no tiene productos asociados. Cargando todos los productos.");
            $productos = Producto::all();
        } else {
            Log::info("✅ Se encontraron " . $productos->count() . " productos para el cliente ID {$clienteId}");
        }

        return response()->json($productos);
    }
}
