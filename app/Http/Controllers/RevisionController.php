<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\OrdenProduccion;
use App\Models\EtapaProduccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RevisionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // Si es administrador, mostrar todas las órdenes y revisiones sin filtro
        if ($usuario->hasRole('administrador')) {
            $revisiones = Revision::latest()->with('orden')->get();
            $ordenes = OrdenProduccion::latest()->take(20)->get();

            return view('revisiones.index', compact('revisiones', 'ordenes'));
        }

        // Obtener etapa "Revisión" asignada al usuario
        $etapa = EtapaProduccion::where('usuario_id', $usuario->id)
            ->where('nombre', 'Revisión')
            ->first();

        // Si no tiene etapa de revisión asignada, retornar sin órdenes
        if (!$etapa) {
            return view('revisiones.index', [
                'revisiones' => Revision::latest()->with('orden')->get(),
                'ordenes' => collect(),
            ]);
        }

        $etapaId = $etapa->id;
        $ordenEtapa = $etapa->orden;

        $ordenes = OrdenProduccion::with('cliente')
            ->whereHas('etapas', function ($q) use ($usuario, $etapaId, $ordenEtapa) {
                $q->where('etapa_produccion_id', $etapaId)
                    ->where('usuario_id', $usuario->id)
                    ->where('estado', 'pendiente')
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
            ->take(20)
            ->get();

        $revisiones = Revision::latest()->with('orden')->get();

        return view('revisiones.index', compact('revisiones', 'ordenes'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'tipo' => 'required|string',
            'comentarios' => 'nullable|string',
            'revisores' => 'required|array',
            'revisores.*.revisado_por' => 'nullable|string',
            'revisores.*.cantidad' => 'nullable|integer|min:1',
            'revisores.*.comentarios' => 'nullable|string',
        ]);

        foreach ($request->revisores as $revisor) {
            if (!empty($revisor['revisado_por']) && !empty($revisor['cantidad'])) {
                Revision::create([
                    'orden_id'     => $request->orden_id,
                    'revisado_por' => $revisor['revisado_por'],
                    'cantidad'     => $revisor['cantidad'],
                    'comentarios'  => $revisor['comentarios'] ?? null,
                    'tipo'         => $request->tipo,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Revisiones registradas correctamente.');
    }

    public function update(Request $request, $id)
    {
        $revision = Revision::findOrFail($id);

        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'tipo' => 'required|string',
            'comentarios' => 'nullable|string',
            'revisado_por' => 'required|string',
            'cantidad' => 'required|integer|min:1',
        ]);

        $revision->update([
            'orden_id'     => $request->orden_id,
            'tipo'         => $request->tipo,
            'comentarios'  => $request->comentarios,
            'revisado_por' => $request->revisado_por,
            'cantidad'     => $request->cantidad,
        ]);

        return redirect()->back()->with('success', 'Revisión actualizada correctamente.');
    }

    public function destroy($id)
    {
        $revision = Revision::findOrFail($id);
        $revision->delete();

        return redirect()->back()->with('success', 'Revisión eliminada correctamente.');
    }


    public function alerta(Request $request, $id)
    {
        $revision = Revision::with('orden')->findOrFail($id);

        if ($revision->orden) {
            $ordenes = Cache::get('toast_revision_ordenes', []);

            // Evitar duplicados
            if (!in_array($revision->orden->numero_orden, $ordenes)) {
                $ordenes[] = $revision->orden->numero_orden;
            }

            // Guardar en cache por 15 minutos
            Cache::put('toast_revision_ordenes', $ordenes, now()->addMinutes(15));
        }

        return redirect()->back();
    }
}
