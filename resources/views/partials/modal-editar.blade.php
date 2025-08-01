<div class="modal fade" id="modalEditar{{ $etiqueta->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $etiqueta->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <form method="POST" action="{{ route('inventario-etiquetas.update', $etiqueta->id) }}" class="w-100">
            @csrf
            @method('PUT')
            <input type="hidden" name="orden_id" value="{{ $etiqueta->orden_id }}">

            <div class="modal-content shadow-lg rounded-4 border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title">
                        <i class="bi bi-shield-lock-fill me-2"></i> Editar Inventario (#{{ $etiqueta->id }})
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body px-4 py-3">

                    {{-- Producto --}}
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        @if($etiqueta->orden_id)
                            <select name="item_orden_id" id="productoSelect{{ $etiqueta->id }}" class="form-select rounded-3" required>
                                <option value="">Cargando productos...</option>
                            </select>
                        @else
                            <select name="producto_id" id="productoSelect{{ $etiqueta->id }}" class="form-select rounded-3" required>
                                <option value="">Cargando productos...</option>
                            </select>
                        @endif
                    </div>

                    {{-- Cantidad --}}
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control rounded-3" value="{{ $etiqueta->cantidad }}" required min="1">
                    </div>

                    {{-- Fecha --}}
                    <div class="mb-3">
                        <label class="form-label">Fecha programada</label>
                        <input type="date" name="fecha_programada" class="form-control rounded-3" value="{{ $etiqueta->fecha_programada }}">
                    </div>

                    {{-- Observaciones --}}
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control rounded-3" rows="2">{{ $etiqueta->observaciones }}</textarea>
                    </div>

                    {{-- Estado --}}
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select rounded-3" required>
                            <option value="pendiente" {{ $etiqueta->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="stock" {{ $etiqueta->estado === 'stock' ? 'selected' : '' }}>Stock</option>
                            <option value="liberado" {{ $etiqueta->estado === 'liberado' ? 'selected' : '' }}>Liberado</option>
                        </select>
                    </div>

                    <hr class="my-3">
                </div>

                <div class="modal-footer bg-light border-top-0 rounded-bottom-4 px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ordenId = {{ $etiqueta->orden_id ?? 'null' }};
        const select = document.getElementById('productoSelect{{ $etiqueta->id }}');
        const selectedItemId = {{ $etiqueta->item_orden_id ?? 'null' }};
        const selectedProductoId = {{ $etiqueta->producto_id ?? 'null' }};

        if (ordenId) {
            // Etiqueta asociada a orden: cargar productos de la orden
            fetch(`/ordenes/${ordenId}/items-json`)
                .then(res => res.json())
                .then(items => {
                    let options = '<option value="">Seleccione un producto</option>';
                    items.forEach(item => {
                        const selected = item.id === selectedItemId ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.nombre}</option>`;
                    });
                    select.innerHTML = options;
                })
                .catch(() => {
                    select.innerHTML = '<option value="">Error al cargar productos</option>';
                });
        } else {
            // Etiqueta libre: cargar productos sueltos
            fetch(`/productos/todos-json`)
                .then(res => res.json())
                .then(productos => {
                    let options = '<option value="">Seleccione un producto</option>';
                    productos.forEach(p => {
                        const selected = p.id === selectedProductoId ? 'selected' : '';
                        options += `<option value="${p.id}" ${selected}>${p.nombre}</option>`;
                    });
                    select.innerHTML = options;
                })
                .catch(() => {
                    select.innerHTML = '<option value="">Error al cargar productos</option>';
                });
        }
    });
</script>
