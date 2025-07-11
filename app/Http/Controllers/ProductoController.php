<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::orderBy('nombre')->get();
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|unique:productos,codigo',
            'nombre' => 'required|string',
            'presentacion' => 'nullable|string',
            'unidad' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = uniqid() . '.' . $imagen->getClientOriginalExtension();

                // Ruta absoluta al directorio público de imágenes
                $rutaDestino = '/home/u646187213/domains/navasan.site/public_html/images/productos';

                // Crear la carpeta si no existe
                if (!file_exists($rutaDestino)) {
                    mkdir($rutaDestino, 0775, true);
                }

                // Mover la imagen a la ruta deseada
                $imagen->move($rutaDestino, $nombreImagen);

                // Guardar ruta accesible públicamente
                $data['imagen'] = 'images/productos/' . $nombreImagen;
            }

            $producto = Producto::create($data);

            if ($request->ajax()) {
                return response()->json($producto);
            }

            return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
        } catch (\Throwable $e) {
            \Log::error('❌ Error al guardar producto: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['message' => 'Error inesperado.'], 500);
            }

            return redirect()->back()->with('error', 'Ocurrió un error inesperado.');
        }
    }
}
