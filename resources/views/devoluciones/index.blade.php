@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 border-0">
        <div class="card-header text-white rounded-top-4 py-3 px-4" style="background-color: #16509D;">
            <h5 class="mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i>Devoluciones por parte del Cliente</h5>
        </div>

        <div class="card-body bg-white px-4 py-4">

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('devoluciones.store') }}" class="mb-4">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Orden de Producción</label>
                        <select
                            name="orden_id"
                            id="orden_id"
                            class="form-select"
                            required
                            data-revisiones-url="{{ route('ordenes.revisiones', ['orden' => '__ID__']) }}">
                            <option value="">Seleccione</option>
                            @foreach($ordenes as $orden)
                            <option value="{{ $orden->id }}">#{{ $orden->numero_orden }} - {{ $orden->cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Revisadora Asignada</label>
                        {{-- Select dinámico con usuarios que revisaron la orden --}}
                        <select name="revisadora_asignada" id="revisadora_asignada" class="form-select" required disabled>
                            <option value="">Seleccione primero una orden</option>
                        </select>
                        <small class="text-muted d-block mt-1" id="revisora_hint">
                            Seleccione una orden para cargar las personas que revisaron.
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Error</label>
                        <input type="text" name="tipo_error" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Motivo de Devolución (Cliente)</label>
                        <textarea name="motivo_cliente" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Comentarios Adicionales</label>
                        <textarea name="comentarios_adicionales" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6 d-flex align-items-center">
                        <input type="checkbox" name="codigo_rojo" value="1" class="form-check-input me-2" id="urgente">
                        <label for="urgente" class="form-check-label"> Marcar como urgente (Código Rojo)</label>
                    </div>

                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                            <i class="bi bi-save me-1"></i> Registrar Devolución
                        </button>
                    </div>

                    {{-- Panel de revisiones (se muestra cuando hay datos) --}}
                    <div class="col-12" id="panelRevisiones" style="display:none;">
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-header bg-light">
                                <strong>Revisiones de la orden seleccionada</strong>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Resultado</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaRevisionesBody">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Sin datos</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <hr>

            <h6 class="text-secondary mb-3">Historial de Devoluciones:</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Cliente</th>
                            <th>Motivo</th>
                            <th>Revisadora</th>
                            <th>Urgente</th>
                            <th>Fecha</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devoluciones as $item)
                        <tr>
                            <td>#{{ $item->orden->numero_orden }}</td>
                            <td>{{ $item->orden->cliente->nombre ?? '-' }}</td>
                            <td>{{ Str::limit($item->motivo_cliente, 40) }}</td>
                            <td>{{ $item->revisadora_asignada }}</td>
                            <td>
                                @if($item->codigo_rojo)
                                <span class="badge bg-danger">Código Rojo</span>
                                @else
                                <span class="badge bg-secondary">Normal</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <form action="{{ route('devoluciones.destroy', $item->id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('¿Eliminar esta devolución? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay devoluciones registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>

{{-- Script: carga revisiones y usuarios que revisaron al seleccionar la orden --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectOrden = document.getElementById('orden_id');
        const selectRevisora = document.getElementById('revisadora_asignada');
        const panelRevisiones = document.getElementById('panelRevisiones');
        const tbody = document.getElementById('tablaRevisionesBody');
        const hint = document.getElementById('revisora_hint');
        const routeTemplate = selectOrden.getAttribute('data-revisiones-url');

        async function cargarRevisiones(ordenId) {
            // Reset UI
            selectRevisora.innerHTML = '<option value="">Cargando...</option>';
            selectRevisora.disabled = true;
            panelRevisiones.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>';

            if (!ordenId) {
                selectRevisora.innerHTML = '<option value="">Seleccione primero una orden</option>';
                hint.textContent = 'Seleccione una orden para cargar las personas que revisaron.';
                return;
            }

            try {
                const url = routeTemplate.replace('__ID__', ordenId);
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('No se pudieron obtener las revisiones');
                const data = await res.json();

                // Poblar select de revisora con los usuarios que revisaron
                const usuarios = data.usuarios || [];
                if (usuarios.length === 0) {
                    selectRevisora.innerHTML = '<option value="">Esta orden no tiene revisiones registradas</option>';
                    selectRevisora.disabled = true;
                    hint.textContent = 'No hay revisiones registradas para esta orden.';
                } else {
                    selectRevisora.innerHTML = '<option value="">Seleccione una persona que revisó</option>';
                    usuarios.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.name; // el backend espera string en "revisadora_asignada"
                        opt.textContent = u.name;
                        selectRevisora.appendChild(opt);
                    });
                    selectRevisora.disabled = false;
                    hint.textContent = 'Selecciona una persona que haya revisado la orden.';
                }

                // Poblar tabla de revisiones
                const revisiones = data.revisiones || [];
                if (revisiones.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin revisiones registradas</td></tr>';
                } else {
                    tbody.innerHTML = '';
                    revisiones.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${r.created_at ?? '-'}</td>
                        <td>${r.usuario ?? '-'}</td>
                        <td>${r.resultado ?? '-'}</td>
                        <td>${r.observaciones ?? '-'}</td>
                    `;
                        tbody.appendChild(tr);
                    });
                }

                panelRevisiones.style.display = 'block';
            } catch (e) {
                console.error(e);
                selectRevisora.innerHTML = '<option value="">Error al cargar</option>';
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Error al cargar revisiones</td></tr>';
                selectRevisora.disabled = true;
                hint.textContent = 'Ocurrió un error cargando la información.';
                panelRevisiones.style.display = 'block';
            }
        }

        // Eventos
        selectOrden.addEventListener('change', (e) => cargarRevisiones(e.target.value));

        // Si viene con un valor preseleccionado (old), intenta cargar
        if (selectOrden.value) {
            cargarRevisiones(selectOrden.value);
        }
    });
</script>
@endsection