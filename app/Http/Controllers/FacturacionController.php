<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenProduccion;
use App\Models\Facturacion;
use PDF;

class FacturacionController extends Controller
{
    public function index()
    {
        $ordenes = OrdenProduccion::latest()->take(15)->get();
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

        // ⚠️ Importante: cargar relaciones antes de generar el PDF
        $factura->load([
            'orden.cliente',
            'orden.items',
            'orden.insumos.insumo',
            'orden.impresiones',
            'orden.acabados',
            'orden.revisiones',
        ]);

        $pdf = PDF::loadView('facturacion-logistica.pdf', compact('factura'));
        return $pdf->download('factura-orden-' . $factura->orden->numero_orden . '.pdf');
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

        // Reemplazar caracteres inválidos para nombres de archivo
        $numeroOrdenLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $factura->orden->numero_orden);

        $pdf = PDF::loadView('facturacion-logistica.pdf', compact('factura'));
        return $pdf->download('factura-orden-' . $numeroOrdenLimpio . '.pdf');
    }
}
