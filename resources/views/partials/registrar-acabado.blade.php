<!-- Modal Registrar Acabado -->
<div class="modal fade" id="modalRegistrarAcabado" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('acabados.store') }}">
            @csrf
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header rounded-top-4 text-white" style="background-color: #003566;">
                    <h5 class="modal-title">Registrar Proceso de Acabado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach(\App\Models\OrdenProduccion::latest()->take(10)->get() as $orden)
                                <option value="{{ $orden->id }}">Orden #{{ $orden->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Proceso</label>
                        <select name="proceso" class="form-select" required>
                            <option value="laminado_mate">Laminado Mate</option>
                            <option value="laminado_brillante">Laminado Brillante</option>
                            <option value="empalmado">Empalmado</option>
                            <option value="suaje">Suaje</option>
                            <option value="corte_guillotina">Corte Guillotina</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Realizado por</label>
                        <input type="text" name="realizado_por" class="form-control" required>
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
