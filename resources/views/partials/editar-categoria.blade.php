<!-- Modal Editar Categoría -->
<div class="modal fade" id="modalEditarCategoria{{ $categoria->id }}" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel{{ $categoria->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('categorias.update', $categoria->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title" id="modalEditarCategoriaLabel{{ $categoria->id }}">
                        Editar Categoría
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ $categoria->nombre }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2">{{ $categoria->descripcion }}</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
