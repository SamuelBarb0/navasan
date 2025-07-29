<!-- Modal Editar Insumo -->
<div class="modal fade" id="editarInsumoModal{{ $insumo->id }}" tabindex="-1" aria-labelledby="editarInsumoLabel{{ $insumo->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('insumos.update', $insumo->id) }}">
            @csrf
            @method('PUT')

            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title" id="editarInsumoLabel{{ $insumo->id }}">
                        Editar Insumo: {{ $insumo->nombre }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ $insumo->nombre }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unidad de medida</label>
                        <input type="text" name="unidad" class="form-control" value="{{ $insumo->unidad }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Seleccione una categoría</option>
                            @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ $insumo->categoria_id == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control">{{ $insumo->descripcion }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad actual</label>
                        <input type="number" name="cantidad_actual" class="form-control"
                            value="{{ $insumo->inventario?->cantidad_disponible ?? 0 }}"
                            step="any" min="0">
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