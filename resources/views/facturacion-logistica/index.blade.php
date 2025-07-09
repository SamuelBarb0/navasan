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

                <div class="row mb-3">
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
    // Mostrar productos de la orden seleccionada
    document.querySelector('select[name="orden_id"]').addEventListener('change', function() {
        const ordenId = this.value;
        const productosDiv = document.getElementById('productosOrden');
        productosDiv.innerHTML = '';

        if (!ordenId) return;

        fetch(`/ordenes/${ordenId}/productos-json`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    productosDiv.innerHTML = `<div class="alert alert-warning mt-2">No hay productos asociados a esta orden.</div>`;
                    return;
                }

                let html = `
        <div class="card mt-3">
            <div class="card-header bg-light fw-semibold">🧾 Productos de la Orden</div>
            <ul class="list-group list-group-flush">
    `;

                let total = 0;
                let sumaCantidad = 0;

                data.forEach(producto => {
                    const precio = parseFloat(producto.precio) || 0;
                    const cantidad = parseFloat(producto.cantidad) || 0;
                    const subtotal = parseFloat(producto.subtotal) || 0;

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
                cantidadInput.value = sumaCantidad;

            })

            .catch(() => {
                productosDiv.innerHTML = `<div class="alert alert-danger mt-2">Error al cargar los productos.</div>`;
            });
    });
</script>

@endsection