@foreach ($revisiones as $revision)
<div class="modal fade" id="modalEditarRevision{{ $revision->id }}" tabindex="-1" aria-labelledby="modalEditarRevisionLabel{{ $revision->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('revisiones.update', $revision->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background-color: #16509D; color: white;">
                    <h5 class="modal-title" id="modalEditarRevisionLabel{{ $revision->id }}">Editar Revisión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <input type="text" class="form-control" value="{{ $revision->orden->numero_orden ?? 'N/A' }}" disabled>
                        <input type="hidden" name="orden_id" value="{{ $revision->orden_id }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre del Revisor</label>
                        <input type="text" name="revisado_por" class="form-control" value="{{ $revision->revisado_por }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad revisada</label>
                        <input type="number" name="cantidad" class="form-control" value="{{ $revision->cantidad }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comentario</label>
                        <textarea name="comentarios" class="form-control" rows="2">{{ $revision->comentarios }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de revisión</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione tipo</option>
                            <option value="correcta"   {{ $revision->tipo == 'correcta' ? 'selected' : '' }}>✅ Correcta</option>
                            <option value="defectos"   {{ $revision->tipo == 'defectos' ? 'selected' : '' }}>⚠️ Defectos pero sirve</option>
                            <option value="apartada"   {{ $revision->tipo == 'apartada' ? 'selected' : '' }}>🟠 Pausada a la espera de aprobación</option>
                            <option value="rechazada"  {{ $revision->tipo == 'rechazada' ? 'selected' : '' }}>❌ Rechazada</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn" style="background-color: #0578BE; color: white;">
                        <i class="bi bi-save2"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
