<div class="modal fade" id="modalRegistrarRevision" tabindex="-1" aria-labelledby="modalRegistrarRevisionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('revisiones.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background-color: #16509D; color: white;">
                    <h5 class="modal-title" id="modalRegistrarRevisionLabel">Registrar Revisión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body" style="background-color: #f8f9fa;">
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach($ordenes as $orden)
                                <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Revisado por</label>
                        <select name="revisado_por" class="form-select" required>
                            <option value="">Seleccione</option>
                            <option value="Dulce">Dulce</option>
                            <option value="Hiromi">Hiromi</option>
                            <option value="Andrea">Andrea</option>
                            <option value="Xonat">Xonat</option>
                            <option value="Dulce 2">Dulce 2</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad revisada</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de revisión</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione tipo</option>
                            <option value="correcta">✅ Correcta</option>
                            <option value="defectos">⚠️ Defectos pero sirve</option>
                            <option value="apartada">🟠 Apartada</option>
                            <option value="rechazada">❌ Rechazada</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comentarios</label>
                        <textarea name="comentarios" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn" style="background-color: #0578BE; color: #ffff;">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
