<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenProduccion;
use App\Models\EtapaProduccion;
use App\Models\Facturacion;
use Illuminate\Support\Facades\DB;
use PDF;

class FacturacionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // 👑 Si es administrador, ve todas las órdenes
        if ($usuario->hasRole('administrador')) {
            $ordenes = OrdenProduccion::latest()->take(15)->get();
            return view('facturacion-logistica.index', compact('ordenes'));
        }

        // Buscar etapa asignada al usuario (ajusta el nombre exacto de la etapa)
        $etapa = EtapaProduccion::where('usuario_id', $usuario->id)
            ->where('nombre', 'Facturación y Logística') // o 'Facturación Logística', según el caso
            ->first();

        // Si no tiene etapa asignada, no ve órdenes
        if (!$etapa) {
            return view('facturacion-logistica.index', [
                'ordenes' => collect(),
            ]);
        }

        $etapaId = $etapa->id;
        $ordenEtapa = $etapa->orden;

        $ordenes = OrdenProduccion::with('cliente')
            ->whereHas('etapas', function ($q) use ($usuario, $etapaId, $ordenEtapa) {
                $q->where('etapa_produccion_id', $etapaId)
                    ->where('usuario_id', $usuario->id)
                    ->whereIn('estado', ['pendiente', 'en_proceso']) // ✅ aquí incluimos ambos
                    ->whereNotExists(function ($subquery) use ($ordenEtapa) {
                        $subquery->select(DB::raw(1))
                            ->from('orden_etapas as anteriores')
                            ->join('etapa_produccions as ep', 'anteriores.etapa_produccion_id', '=', 'ep.id')
                            ->whereColumn('anteriores.orden_produccion_id', 'orden_etapas.orden_produccion_id')
                            ->where('ep.orden', '<', $ordenEtapa)
                            ->whereIn('anteriores.estado', ['pendiente', 'en_proceso']);
                    });
            })
            ->latest()
            ->take(15)
            ->get();

        return view('facturacion-logistica.index', compact('ordenes'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'cantidad_final' => 'required|numeric|min:1',
            'costo_unitario' => 'nullable|numeric|min:0',
            'estado_facturacion' => 'required|in:pendiente,facturado,entregado',
            'fecha_entrega' => 'required|date',
            'metodo_entrega' => 'nullable|string|max:255',
        ]);

        $factura = new Facturacion();
        $factura->orden_id = $validated['orden_id'];
        $factura->cantidad_final = $validated['cantidad_final'];
        $factura->costo_unitario = $validated['costo_unitario'] ?? 0;
        $factura->total = $factura->cantidad_final * $factura->costo_unitario;
        $factura->estado_facturacion = $validated['estado_facturacion'];
        $factura->fecha_entrega = $validated['fecha_entrega'];
        $factura->metodo_entrega = $validated['metodo_entrega'];
        $factura->save();

        $factura->load([
            'orden.cliente',
            'orden.items',
            'orden.insumos.insumo',
            'orden.impresiones',
            'orden.acabados',
            'orden.revisiones',
        ]);

        $numeroOrdenLimpio = preg_replace('/[\/\\\\:*?"<>|]/', '-', $factura->orden->numero_orden);

        $pdf = PDF::loadView('facturacion-logistica.pdf', compact('factura'));
        return $pdf->download('factura-orden-' . $numeroOrdenLimpio . '.pdf');
    }

    public function descargarFactura($id)
    {
        $factura = Facturacion::with([
            'orden.cliente',
            'orden.items',
            'orden.insumos.insumo',
            'orden.impresiones',
            'orden.acabados',
            'orden.revisiones',
        ])->findOrFail($id);

        // Limpiar caracteres no válidos
        $numeroOrdenLimpio = preg_replace('/[\/\\\\:*?"<>|]/', '-', $factura->orden->numero_orden);

        $pdf = PDF::loadView('facturacion-logistica.pdf', compact('factura'));
        return $pdf->download('factura-orden-' . $numeroOrdenLimpio . '.pdf');
    }
}
