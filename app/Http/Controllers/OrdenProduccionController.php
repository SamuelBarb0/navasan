<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdenProduccion;
use App\Models\ItemOrden;
use App\Models\EtapaProduccion;
use App\Models\OrdenEtapa;
use App\Models\ItemEntrega;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class OrdenProduccionController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $usuario = auth()->user();
        $esAdmin = $usuario->hasRole('administrador'); // ← aquí usamos Spatie correctamente

        // Órdenes normales
        $ordenes = \App\Models\OrdenProduccion::with('cliente')
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('numero_orden', 'like', "%$busqueda%")
                        ->orWhereHas('items.producto', function ($q) use ($busqueda) {
                            $q->where('codigo', 'like', "%$busqueda%");
                        });
                });
            })
            ->when(!$esAdmin, function ($query) use ($usuario) {
                $query->whereHas('etapas', function ($q) use ($usuario) {
                    $q->where('usuario_id', $usuario->id);
                });
            })
            ->where(function ($q) {
                $q->where('urgente', false)->orWhereNull('urgente');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Devoluciones urgentes
        $devoluciones = \App\Models\OrdenProduccion::with('cliente')
            ->where('urgente', true)
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('numero_orden', 'like', "%$busqueda%")
                        ->orWhereHas('items.producto', function ($q) use ($busqueda) {
                            $q->where('codigo', 'like', "%$busqueda%");
                        });
                });
            })
            ->when(!$esAdmin, function ($query) use ($usuario) {
                $query->whereHas('etapas', function ($q) use ($usuario) {
                    $q->where('usuario_id', $usuario->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ordenes.index', compact('ordenes', 'devoluciones', 'esAdmin'));
    }

    public function show($id)
    {
        $orden = \App\Models\OrdenProduccion::with([
            'cliente',
            'items.entregas',
            'etapas.etapa',
            'etapas.usuario'
        ])->findOrFail($id);

        $usuario = auth()->user();
        $esAdmin = $usuario->hasRole('administrador');

        return view('ordenes.show', compact('orden', 'usuario', 'esAdmin'));
    }


    public function create()
    {
        $clientes = Cliente::all();
        $productos = \App\Models\Producto::where('activo', true)->get();

        return view('ordenes.create', compact('clientes', 'productos'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_orden' => 'required|unique:orden_produccions',
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'nullable|exists:productos,id',
            'items.*.nombre' => 'required|string',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.entregas' => 'nullable|array',
            'items.*.entregas.*.fecha' => 'required|date',
            'items.*.entregas.*.cantidad' => 'required|integer|min:1',
        ]);

        // Crear la orden
        $orden = OrdenProduccion::create([
            'cliente_id' => $data['cliente_id'],
            'numero_orden' => $data['numero_orden'],
            'fecha' => $data['fecha'],
            'estado' => 'pendiente',
        ]);

        // Crear los ítems y sus entregas
        foreach ($data['items'] as $item) {
            $itemOrden = ItemOrden::create([
                'orden_produccion_id' => $orden->id,
                'producto_id' => $item['producto_id'] ?? null,
                'nombre' => $item['nombre'],
                'cantidad' => $item['cantidad'],
            ]);

            if (!empty($item['entregas'])) {
                foreach ($item['entregas'] as $entrega) {
                    ItemEntrega::create([
                        'item_orden_id' => $itemOrden->id,
                        'fecha_entrega' => $entrega['fecha'],
                        'cantidad' => $entrega['cantidad'],
                    ]);
                }
            }
        }

        // Crear etapas de producción asociadas
        $etapas = EtapaProduccion::orderBy('orden')->get();
        foreach ($etapas as $etapa) {
            OrdenEtapa::create([
                'orden_produccion_id' => $orden->id,
                'etapa_produccion_id' => $etapa->id,
                'estado' => 'pendiente',
                'usuario_id' => $etapa->usuario_id,
            ]);
        }

        return redirect()->route('ordenes.index')->with('success', 'Orden creada con sus productos, entregas y etapas correctamente.');
    }

    public function productosDeOrden($id)
    {
        $orden = \App\Models\OrdenProduccion::with('items.producto')->findOrFail($id);

        $productos = $orden->items->map(function ($item) {
            $precio = $item->producto->precio ?? 0;
            $cantidad = $item->cantidad ?? 0;

            return [
                'nombre' => $item->producto->nombre ?? $item->nombre ?? 'Producto sin nombre',
                'precio' => $precio,
                'cantidad' => $cantidad,
                'subtotal' => $precio * $cantidad,
            ];
        });

        return response()->json($productos);
    }

    public function itemsJson($id)
{
    $orden = \App\Models\OrdenProduccion::with('items')->findOrFail($id);
    return response()->json(
        $orden->items->map(fn($item) => [
            'id' => $item->id,
            'nombre' => $item->nombre,
        ])
    );
}

}
