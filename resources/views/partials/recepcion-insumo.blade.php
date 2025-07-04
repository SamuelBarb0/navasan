<!-- Modal Recepción de Insumo -->
<div class="modal fade" id="modalRecepcionInsumo" tabindex="-1" aria-labelledby="modalRecepcionInsumoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('insumos.recepcion.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="insumo_id" id="recepcion_insumo_id">

            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header rounded-top-4 text-white" style="background-color: #F2A700;">
                    <h5 class="modal-title" id="modalRecepcionInsumoLabel"><i class="bi bi-truck me-1"></i> Recepción de Insumo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Tipo de recepción</label>
                        <input type="text" class="form-control" name="tipo_recepcion" placeholder="Compra, Inventario, Donación..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad recibida</label>
                        <input type="number" class="form-control" name="cantidad_recibida" step="any" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de recepción</label>
                        <input type="date" class="form-control" name="fecha_recepcion" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Factura / Remisión (opcional)</label>
                        <input type="file" class="form-control" name="factura_archivo" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #16509D;">
                        <i class="bi bi-check-circle"></i> Registrar recepción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
