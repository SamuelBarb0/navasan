@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 border-0">
        <div class="card-header text-white rounded-top-4 py-3 px-4" style="background-color: #16509D;">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Facturación y Logística</h5>
        </div>

        <div class="card-body bg-white px-4 py-4">
            <form method="POST" action="{{ route('facturacion.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach($ordenes as $orden)
                                <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Cantidad Final Producida</label>
                        <input type="number" name="cantidad_final" class="form-control" required>
                    </div>
                </div>

                {{-- Productos asociados --}}
                <div id="productosOrden" class="mt-3"></div>

                {{-- 👇 NUEVO: Revisiones de la orden --}}
                <div id="revisionesOrden" class="mt-3"></div>

                <div class="row mb-3 mt-4">
                    <div class="col-md-6">
                        <label class="form-label">Estado de Facturación</label>
                        <select name="estado_facturacion" class="form-select" required>
                            <option value="pendiente">🕒 Pendiente</option>
                            <option value="facturado">💼 Facturado</option>
                            <option value="entregado">🚚 Entregado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fecha de entrega</label>
                        <input type="date" name="fecha_entrega" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Comentarios</label>
                    <input type="text" name="metodo_entrega" class="form-control" placeholder="Observaciones o notas adicionales...">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle me-1"></i> Guardar Facturación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const selOrden     = document.querySelector('select[name="orden_id"]');
const productosDiv = document.getElementById('productosOrden');
const revisionesDiv= document.getElementById('revisionesOrden');

selOrden.addEventListener('change', function() {
    const ordenId = this.value;
    productosDiv.innerHTML  = '';
    revisionesDiv.innerHTML = '';

    if (!ordenId) return;

    // 1) Productos de la orden
    fetch(`/ordenes/${ordenId}/productos-json`)
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                productosDiv.innerHTML = `<div class="alert alert-warning mt-2">No hay productos asociados a esta orden.</div>`;
                // aunque no haya productos, igual mostramos revisiones (pueden existir)
            } else {
                let html = `
                    <div class="card mt-3">
                        <div class="card-header bg-light fw-semibold">🧾 Productos de la Orden</div>
                        <ul class="list-group list-group-flush">
                `;
                let total = 0;
                let sumaCantidad = 0;

                data.forEach(producto => {
                    const precio   = parseFloat(producto.precio)   || 0;
                    const cantidad = parseFloat(producto.cantidad) || 0;
                    const subtotal = parseFloat(producto.subtotal) || (precio * cantidad);

                    total += subtotal;
                    sumaCantidad += cantidad;

                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${producto.nombre}</strong><br>
                                <small class="text-muted">Cantidad: ${cantidad}</small>
                            </div>
                            <div class="text-end">
                                <div>$${precio.toFixed(2)} c/u</div>
                                <small class="text-muted">Subtotal: $${subtotal.toFixed(2)}</small>
                            </div>
                        </li>
                    `;
                });

                html += `
                        </ul>
                        <div class="card-footer text-end fw-bold">
                            Total Estimado: $${total.toFixed(2)}
                        </div>
                    </div>
                `;
                productosDiv.innerHTML = html;

                // ✅ Asignar cantidad total automáticamente
                const cantidadInput = document.querySelector('input[name="cantidad_final"]');
                if (cantidadInput) cantidadInput.value = sumaCantidad;
            }

            // 2) Revisiones de la orden (siempre consultamos)
            cargarRevisionesDeOrden(ordenId);
        })
        .catch(() => {
            productosDiv.innerHTML = `<div class="alert alert-danger mt-2">Error al cargar los productos.</div>`;
            // Igual intentamos traer revisiones
            cargarRevisionesDeOrden(ordenId);
        });
});

// Función para cargar y renderizar revisiones
function cargarRevisionesDeOrden(ordenId) {
    revisionesDiv.innerHTML = `
        <div class="card mt-3">
            <div class="card-header bg-light fw-semibold">🔎 Revisiones de la Orden</div>
            <div class="card-body">
                <div class="text-muted">Cargando revisiones…</div>
            </div>
        </div>
    `;

    fetch(`/ordenes/${ordenId}/revisiones-json`)
        .then(res => res.json())
        .then(revisiones => {
            if (!Array.isArray(revisiones) || revisiones.length === 0) {
                revisionesDiv.innerHTML = `
                    <div class="card mt-3">
                        <div class="card-header bg-light fw-semibold">🔎 Revisiones de la Orden</div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">No hay revisiones registradas para esta orden.</div>
                        </div>
                    </div>
                `;
                return;
            }

            // Mapa de badges por tipo
            const tipoBadge = {
                'correcta' : 'badge bg-success',
                'defectos' : 'badge bg-warning text-dark',
                'apartada' : 'badge bg-orange text-dark', // si no existe tu css "bg-orange", usa 'bg-warning'
                'rechazada': 'badge bg-danger',
            };

            // Suma total revisada
            let totalRevisado = 0;

            let rows = revisiones.map(r => {
                const cant  = Number(r.cantidad) || 0;
                totalRevisado += cant;

                const tipo = (r.tipo || '').toLowerCase();
                const badgeClass = tipoBadge[tipo] || 'badge bg-secondary';

                const fecha = r.fecha
                    ? new Date(r.fecha).toLocaleDateString()
                    : (r.created_at ? new Date(r.created_at).toLocaleDateString() : '-');

                const comentario = r.comentarios || r.comentario || '—';
                const nombre = r.revisado_por || r.revisor || '—';

                return `
                    <tr>
                        <td>${nombre}</td>
                        <td class="text-end">${cant}</td>
                        <td><span class="${badgeClass}">${tipo ? tipo.charAt(0).toUpperCase() + tipo.slice(1) : '—'}</span></td>
                        <td>${comentario}</td>
                        <td class="text-nowrap text-muted">${fecha}</td>
                    </tr>
                `;
            }).join('');

            revisionesDiv.innerHTML = `
                <div class="card mt-3">
                    <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
                        <span>🔎 Revisiones de la Orden</span>
                        <span class="small text-muted">Total revisado: <strong>${totalRevisado}</strong></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Revisor</th>
                                    <th class="text-end">Cantidad</th>
                                    <th>Tipo</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            `;
        })
        .catch(() => {
            revisionesDiv.innerHTML = `
                <div class="card mt-3">
                    <div class="card-header bg-light fw-semibold">🔎 Revisiones de la Orden</div>
                    <div class="card-body">
                        <div class="alert alert-danger mb-0">Error al cargar las revisiones.</div>
                    </div>
                </div>
            `;
        });
}
</script>

@endsection
