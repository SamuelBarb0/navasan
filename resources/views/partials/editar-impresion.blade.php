<!-- Modal Editar Impresión -->
<div class="modal fade" id="modalEditarImpresion{{ $item->id }}" tabindex="-1" aria-labelledby="editarImpresionLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('impresiones.update', $item->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title" id="editarImpresionLabel{{ $item->id }}">Editar Impresión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <select name="orden_id" class="form-select" required>
                            @foreach(\App\Models\OrdenProduccion::all() as $orden)
                                <option value="{{ $orden->id }}" {{ $orden->id == $item->orden_id ? 'selected' : '' }}>
                                    Orden #{{ $orden->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Impresión</label>
                        <input type="text" name="tipo_impresion" class="form-control" value="{{ $item->tipo_impresion }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Máquina</label>
                        <input type="text" name="maquina" class="form-control" value="{{ $item->maquina }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad de pliegos</label>
                        <input type="number" name="cantidad_pliegos" class="form-control" value="{{ $item->cantidad_pliegos }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Inicio</label>
                        <input type="datetime-local" name="inicio_impresion" class="form-control"
                               value="{{ \Carbon\Carbon::parse($item->inicio_impresion)->format('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fin</label>
                        <input type="datetime-local" name="fin_impresion" class="form-control"
                               value="{{ \Carbon\Carbon::parse($item->fin_impresion)->format('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="espera" {{ $item->estado == 'espera' ? 'selected' : '' }}>🟠 En espera</option>
                            <option value="proceso" {{ $item->estado == 'proceso' ? 'selected' : '' }}>🔵 En proceso</option>
                            <option value="completado" {{ $item->estado == 'completado' ? 'selected' : '' }}>🟢 Completado</option>
                            <option value="rechazado" {{ $item->estado == 'rechazado' ? 'selected' : '' }}>🔴 Rechazado</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
