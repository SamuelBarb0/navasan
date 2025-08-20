<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\OrdenProduccion;
use App\Models\EtapaProduccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str; // 👈 AÑADE ESTO

class RevisionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // ⬇️ NUEVO: órdenes en cache para el toast (15 min)
        $ordenesToast = collect(Cache::get('toast_revision_ordenes', []));

        // 👑 Administrador: ver todo sin filtro
        if ($usuario->hasRole('administrador')) {
            $revisiones = Revision::latest()->with('orden')->get();
            $ordenes = OrdenProduccion::latest()->take(20)->get();
            return view('revisiones.index', compact('revisiones', 'ordenes', 'ordenesToast'));
        }

        // Etapa "Revisión" (no importa a quién esté asignada)
        $etapa = EtapaProduccion::where('nombre', 'Revisión')->first();

        if (!$etapa) {
            return view('revisiones.index', [
                'revisiones'   => Revision::latest()->with('orden')->get(),
                'ordenes'      => collect(),
                'ordenesToast' => $ordenesToast,
            ]);
        }

        $etapaId    = $etapa->id;
        $ordenEtapa = $etapa->orden;

        $ordenes = OrdenProduccion::with('cliente')
            // ✅ debe tener la etapa de Revisión pendiente/en_proceso
            ->whereHas('etapas', function ($q) use ($etapaId) {
                $q->where('etapa_produccion_id', $etapaId)
                    ->whereIn('estado', ['pendiente', 'en_proceso']);
            })
            // ❌ no debe tener etapas anteriores pendientes/en_proceso
            ->whereDoesntHave('etapas', function ($q) use ($ordenEtapa) {
                $q->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->whereHas('etapa', function ($sub) use ($ordenEtapa) {
                        $sub->where('orden', '<', $ordenEtapa);
                    });
            })
            ->latest()
            ->take(20)
            ->get();

        $revisiones = Revision::latest()->with('orden')->get();

        return view('revisiones.index', compact('revisiones', 'ordenes', 'ordenesToast'));
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
            $lista = $request->session()->get('mostrar_toast_revision', []);
            $numero = $revision->orden->numero_orden;

            if (!in_array($numero, $lista, true)) {
                $lista[] = $numero;
            }

            // persiste hasta que lo limpies
            $request->session()->put('mostrar_toast_revision', $lista);
        }

        return back();
    }
}
