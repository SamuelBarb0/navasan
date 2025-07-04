<!-- Modal Crear Insumo -->
<div class="modal fade" id="modalCrearInsumo" tabindex="-1" aria-labelledby="modalCrearInsumoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('insumos.store') }}">
            @csrf
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title" id="modalCrearInsumoLabel">Nuevo Insumo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del insumo</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="unidad" class="form-label">Unidad de medida</label>
                        <input type="text" class="form-control" name="unidad" placeholder="ml, metros, rollos..." required>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad inicial</label>
                        <input type="number" step="any" min="0" class="form-control" name="cantidad" required>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
