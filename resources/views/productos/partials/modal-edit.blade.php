<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 shadow-sm">
      <form id="formEditarProducto" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
          <h5 class="modal-title" id="modalEditarProductoLabel">
            <i class="bi bi-pencil-square me-2"></i> Editar Producto3
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body" style="background-color: #f9fbfd;">
          <input type="hidden" name="producto_id" id="editar_producto_id">

          <div class="mb-3">
            <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
            <input type="text" name="codigo" class="form-control" required style="border-color: #7CB9E6;">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" required style="border-color: #7CB9E6;">
          </div>

          <div class="mb-3">
            <label class="form-label">Presentación</label>
            <input type="text" name="presentacion" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Unidad</label>
            <input type="text" name="unidad" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Cliente</label>
            <select name="producto_cliente" id="producto_cliente_editar" class="form-select">
              <option value="">-- Seleccionar cliente --</option>
              @foreach($clientes as $cliente)
              <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
              @endforeach
            </select>
          </div>

          @hasanyrole('logistica|administrador')
          <div class="mb-3">
            <label class="form-label fw-semibold">Precio <span class="text-danger">*</span></label>
            <input type="number" name="precio" step="0.01" class="form-control" required placeholder="Ej. 19.99">
          </div>
          @endhasanyrole

          <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
            <div id="previewImagen" class="mt-2"></div>
          </div>
        </div>

        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="submit" class="btn text-white" style="background-color: #0578BE;">
            <i class="bi bi-check-circle me-1"></i> Actualizar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
