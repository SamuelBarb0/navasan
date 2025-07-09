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

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">💰 Precio por Unidad</label>
                        <input type="number" name="costo_unitario" step="0.01" class="form-control" placeholder="Ej: 1200.50">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">🧾 Precio Total Estimado</label>
                        <input type="text" id="total_estimado" class="form-control bg-light" readonly placeholder="$0.00">
                        <input type="hidden" name="total" id="total_hidden">
                    </div>
                </div>

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
                    <label class="form-label">Método de entrega</label>
                    <input type="text" name="metodo_entrega" class="form-control" placeholder="Transporte, envío, recogida...">
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
    document.querySelector('input[name="cantidad_final"]').addEventListener('input', calcularTotal);
    document.querySelector('input[name="costo_unitario"]').addEventListener('input', calcularTotal);

    function calcularTotal() {
        const cantidad = parseFloat(document.querySelector('input[name="cantidad_final"]').value) || 0;
        const costo = parseFloat(document.querySelector('input[name="costo_unitario"]').value) || 0;
        const total = cantidad * costo;

        document.getElementById('total_estimado').value = "$" + total.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);
    }
</script>
@endsection
