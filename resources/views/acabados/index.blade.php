@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$routeBase  = Str::beforeLast(Route::currentRouteName(), '.');
$routeStore = $routeBase . '.store';
$routeUpdate= $routeBase . '.update';

$routeName  = Route::currentRouteName();
$tipo       = Str::beforeLast($routeName, '.'); // 'suaje-corte' | 'laminado' | 'empalmado'
$esSuaje    = $tipo === 'suaje-corte';

// Para los componentes de toast parametrizados
$tipoToast  = $esSuaje ? 'suaje' : $tipo; // suaje | laminado | empalmado
@endphp

<div class="container mt-5">
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
      <h4 class="mb-0">
        <i class="bi bi-scissors me-2"></i> {{ $titulo ?? 'Procesos de Acabado' }}
      </h4>
      <button class="btn btn-sm text-white" style="background-color: #0578BE;" data-bs-toggle="modal" data-bs-target="#modalRegistrarAcabado">
        <i class="bi bi-plus-circle"></i> Nuevo
      </button>
    </div>

    <div class="card-body bg-light rounded-bottom-4">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if($acabados->isEmpty())
        <div class="alert alert-info">No hay procesos registrados aún.</div>
      @else
        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle shadow-sm">
            <thead style="background-color: #7CB9E6;" class="text-dark">
              <tr class="text-center">
                <th>Orden</th>

                @if($esSuaje)
                  <th>Cantidad Recibida</th>
                  <th>Cantidad Final</th>
                  <th>Fecha</th>
                @else
                  <th>Proceso</th>
                  <th>Realizado por</th>
                  <th>Producto</th>
                  <th>Cantidad Recibida</th>
                  <th>Cantidad Final</th>
                  <th>Fecha</th>
                  <th>Fecha Fin</th>
                @endif

                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($acabados as $ac)
                @php
                  // Mapear recibida/final según tipo
                  $recibida = $esSuaje ? ($ac->cantidad_pliegos_impresos ?? 0) : ($ac->cantidad_liberada ?? 0);
                  $final    = $esSuaje ? ($ac->cantidad_liberada ?? 0)        : ($ac->cantidad_pliegos_impresos ?? 0);
                  $desfase  = is_numeric($recibida) && is_numeric($final) && $recibida > 0 && $final !== $recibida;
                @endphp
                <tr class="text-center {{ $desfase ? 'table-danger' : '' }}">
                  <td>
                    {{ $ac->orden->numero_orden ?? '—' }}
                    @if($desfase)
                      <span class="badge text-bg-danger ms-2">Desfase</span>
                    @endif
                  </td>

                  @if($esSuaje)
                    {{-- Suaje: Recibida = cantidad_pliegos_impresos | Final = cantidad_liberada --}}
                    <td>{{ $recibida }}</td>
                    <td>{{ $final }}</td>
                    <td>{{ optional($ac->created_at)->format('d/m/Y H:i') }}</td>
                  @else
                    {{-- Laminado/Empalmado: Recibida = cantidad_liberada | Final = cantidad_pliegos_impresos --}}
                    <td class="fw-semibold">
                      {{ $ac->proceso_nombre ?? \Illuminate\Support\Str::of($ac->proceso)->replace('_',' ')->title() }}
                    </td>
                    <td>{{ $ac->realizado_por }}</td>
                    <td>{{ $ac->producto->nombre ?? '—' }}</td>
                    <td>{{ $recibida ?? '—' }}</td>
                    <td>{{ $final ?? '—' }}</td>
                    <td>{{ optional($ac->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $ac->fecha_fin ? \Carbon\Carbon::parse($ac->fecha_fin)->format('d/m/Y H:i') : '—' }}</td>
                  @endif

                  <td>
                    @if($esSuaje)
                      <button
                        class="btn btn-sm btn-warning text-white"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarAcabado"
                        onclick='cargarEdicion(
                          @json($ac->id),
                          @json($ac->orden_id),
                          null, null, null, null,
                          @json($ac->cantidad_liberada),         // final
                          null,
                          @json($ac->cantidad_pliegos_impresos)   // recibida
                        )'>
                        <i class="bi bi-pencil-square"></i> Editar
                      </button>
                    @else
                      <button
                        class="btn btn-sm btn-warning text-white"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarAcabado"
                        onclick='cargarEdicion(
                          @json($ac->id),
                          @json($ac->orden_id),
                          @json($ac->producto_id),
                          @json($ac->proceso),
                          @json($ac->realizado_por),
                          @json(optional($ac->fecha_fin)?->format("Y-m-d\TH:i")),
                          @json($ac->cantidad_pliegos_impresos),  // final
                          null,
                          @json($ac->cantidad_liberada)           // recibida
                        )'>
                        <i class="bi bi-pencil-square"></i> Editar
                      </button>
                    @endif

                    <form action="{{ route($routeBase . '.destroy', $ac->id) }}"
                          method="POST"
                          style="display:inline-block"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este registro?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Borrar
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        @if(method_exists($acabados, 'links'))
          <div class="mt-3">
            {{ $acabados->links() }}
          </div>
        @endif
      @endif
    </div>
  </div>
</div>

<script>
  // Expón valores que usan los parciales
  window.updateUrlTemplate = @json(route($routeUpdate, ['id' => '__ID__']));
  window.ES_SUAJE = @json($esSuaje);
</script>

{{-- Pasamos $tipo para que los parciales sepan si es suaje/laminado/empalmado --}}
@include('partials.registrar-acabado', [
  'action'    => route($routeStore),
  'procesos'  => $procesos ?? [],
  'ordenes'   => $ordenes ?? null,
  'productos' => $productos ?? null,
  'tipo'      => $tipo
])

@include('partials.editar-acabado', [
  'procesos'  => $procesos ?? [],
  'ordenes'   => $ordenes ?? null,
  'productos' => $productos ?? null,
  'tipo'      => $tipo
])
@endsection
