<div class="modal fade" id="modalRegistrarRevision" tabindex="-1" aria-labelledby="modalRegistrarRevisionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('revisiones.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background-color: #16509D; color: white;">
                    <h5 class="modal-title" id="modalRegistrarRevisionLabel">Registrar Revisión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body" style="background-color: #f8f9fa;">
                    <!-- Orden de Producción -->
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach($ordenes as $orden)
                            <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <h6 class="text-primary">Revisores (hasta 5)</h6>

                    @for($i = 0; $i < 5; $i++)
                        <div class="row border rounded p-2 mb-3 bg-white shadow-sm align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Nombre del revisor {{ $i+1 }}</label>
                                <input type="text" name="revisores[{{ $i }}][revisado_por]" class="form-control" placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cantidad revisada</label>
                                <input type="number" name="revisores[{{ $i }}][cantidad]" class="form-control" min="1">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Comentario</label>
                                <input type="text" name="revisores[{{ $i }}][comentarios]" class="form-control" placeholder="Observaciones (opcional)">
                            </div>
                        </div>
                    @endfor

                    <!-- Tipo de revisión -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de revisión</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione tipo</option>
                            <option value="correcta">✅ Correcta</option>
                            <option value="defectos">⚠️ Defectos pero sirve</option>
                            <option value="apartada">🟠 Pausada a la espera de aprobación</option>
                            <option value="rechazada">❌ Rechazada</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn" style="background-color: #0578BE; color: #fff;">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
