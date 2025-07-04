<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdenProduccion;
use App\Models\ItemOrden;
use App\Models\EtapaProduccion;
use App\Models\OrdenEtapa;
use App\Models\ItemEntrega;


use Illuminate\Http\Request;

class OrdenProduccionController extends Controller
{
    public function index()
    {
        $ordenes = \App\Models\OrdenProduccion::with('cliente')->orderBy('created_at', 'desc')->get();
        return view('ordenes.index', compact('ordenes'));
    }

    public function show($id)
    {
        $orden = \App\Models\OrdenProduccion::with([
            'cliente',
            'items.entregas',     // ← importante si quieres ver entregas también
            'etapas.etapa',
            'etapas.usuario'
        ])->findOrFail($id);
        
        return view('ordenes.show', compact('orden'));
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
}
