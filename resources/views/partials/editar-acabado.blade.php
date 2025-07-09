<!-- Modal Editar Acabado -->
<div class="modal fade" id="modalEditarAcabado" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formEditarAcabado">
            @csrf
            @method('PUT')
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header rounded-top-4 text-white" style="background-color: #ffc107;">
                    <h5 class="modal-title text-dark">Editar Proceso de Acabado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" id="edit_orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach(\App\Models\OrdenProduccion::latest()->take(10)->get() as $orden)
                                <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Proceso</label>
                        <select name="proceso" id="edit_proceso" class="form-select" required>
                            <option value="laminado_mate">Laminado Mate</option>
                            <option value="laminado_brillante">Laminado Brillante</option>
                            <option value="empalmado">Empalmado</option>
                            <option value="suaje">Suaje</option>
                            <option value="corte_guillotina">Corte Guillotina</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Realizado por</label>
                        <input type="text" name="realizado_por" id="edit_realizado_por" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha Fin (opcional)</label>
                        <input type="datetime-local" name="fecha_fin" id="edit_fecha_fin" class="form-control">
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #ffc107;">
                        <i class="bi bi-save"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>