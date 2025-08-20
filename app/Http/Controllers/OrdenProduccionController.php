<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdenProduccion;
use App\Models\ItemOrden;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\EtapaProduccion;
use App\Models\OrdenEtapa;
use App\Models\ItemEntrega;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class OrdenProduccionController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $usuario  = auth()->user();

        // Etapas asignadas al usuario (en la PLANTILLA)
        $etapasAsignadas = \App\Models\EtapaProduccion::where('usuario_id', $usuario->id)->get();

        // Si no tiene etapas asignadas y no es admin, no habrá nada que mostrar
        $etapaIds = $etapasAsignadas->pluck('id')->all();
        $minOrden = $etapasAsignadas->min('orden'); // p. ej. 9 para Odila

        // Filtro SOLO para no-admin: 
        // - debe existir una orden_etapas de alguna etapa del usuario en (pendiente|en_proceso)
        // - no debe existir NINGUNA etapa anterior (< minOrden) en (pendiente|en_proceso)
        $filtroEtapas = function ($query) use ($etapaIds, $minOrden, $usuario) {
            // si por alguna razón no tiene etapas, deja vacío
            if (empty($etapaIds)) {
                $query->whereRaw('1=0');
                return;
            }

            $query
                // 1) La orden debe tener la etapa del usuario en estado trabajable
                ->whereHas('etapas', function ($q) use ($etapaIds) {
                    $q->whereIn('etapa_produccion_id', $etapaIds)
                        ->whereIn('estado', ['pendiente', 'en_proceso']);
                })
                // 2) Y NO debe tener etapas anteriores (< minOrden) aún pendientes/en_proceso
                ->whereDoesntHave('etapas', function ($q) use ($minOrden) {
                    $q->whereIn('estado', ['pendiente', 'en_proceso'])
                        ->whereHas('etapa', function ($sub) use ($minOrden) {
                            $sub->where('orden', '<', $minOrden);
                        });
                });
        };

        // Órdenes normales
        $ordenes = \App\Models\OrdenProduccion::with('cliente')
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('numero_orden', 'like', "%{$busqueda}%")
                        ->orWhereHas('items.producto', function ($q) use ($busqueda) {
                            $q->where('codigo', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when(!$usuario->hasRole('administrador'), $filtroEtapas)
            ->where(function ($q) {
                $q->where('urgente', false)->orWhereNull('urgente');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Devoluciones urgentes
        $devoluciones = \App\Models\OrdenProduccion::with(['cliente', 'devolucion'])
            ->where('urgente', true)
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('numero_orden', 'like', "%{$busqueda}%")
                        ->orWhereHas('items.producto', function ($q) use ($busqueda) {
                            $q->where('codigo', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when(!$usuario->hasRole('administrador'), $filtroEtapas)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ordenes.index', compact('ordenes', 'devoluciones'));
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
        $categorias = Categoria::orderBy('nombre')->get();

        return view('ordenes.show', compact('orden', 'usuario', 'esAdmin', 'categorias'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::all();

        $cliente_id = $request->get('cliente_id');

        $productos = Producto::when($cliente_id, function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })->get();

        $etapas = EtapaProduccion::all();

        return view('ordenes.create', compact('clientes', 'productos', 'cliente_id', 'etapas'));
    }

    public function store(Request $request)
    {
        // Validación general (sin validar aún el número duplicado)
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_orden' => 'required|string',
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'nullable|exists:productos,id',
            'items.*.nombre' => 'required|string',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.entregas' => 'nullable|array',
            'items.*.entregas.*.fecha' => 'required|date',
            'items.*.entregas.*.cantidad' => 'required|integer|min:1',
            'etapas' => 'required|array|min:1',
            'etapas.*' => 'exists:etapa_produccions,id',
        ]);

        // Verificar si ya existe una orden con el mismo número
        if (OrdenProduccion::where('numero_orden', $data['numero_orden'])->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('warning', '⚠️ Ya existe una orden con el número <strong>#' . $data['numero_orden'] . '</strong>. Por favor usa otro.');
        }

        // Crear la orden
        $orden = OrdenProduccion::create([
            'cliente_id' => $data['cliente_id'],
            'numero_orden' => $data['numero_orden'],
            'fecha' => $data['fecha'],
            'estado' => 'pendiente',
        ]);

        // Resto igual...
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

        foreach ($data['etapas'] as $etapaId) {
            $etapa = EtapaProduccion::find($etapaId);

            OrdenEtapa::create([
                'orden_produccion_id' => $orden->id,
                'etapa_produccion_id' => $etapa->id,
                'estado' => 'pendiente',
                'usuario_id' => $etapa->usuario_id,
            ]);
        }

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden creada con sus productos, entregas y etapas correctamente.');
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

    public function destroy($id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        // Si necesitas lógica extra de validación/autorización, agrégala aquí

        $orden->delete();

        return redirect()->route('ordenes.index')->with('success', 'Orden eliminada correctamente.');
    }

    public function revisionesJson(\App\Models\OrdenProduccion $orden)
    {
        // Ajusta los nombres según tu modelo/relación
        $revisiones = $orden->revisiones()
            ->select('revisado_por', 'cantidad', 'comentarios', 'tipo', 'created_at as fecha')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($revisiones);
    }
}
