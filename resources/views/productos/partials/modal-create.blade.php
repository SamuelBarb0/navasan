<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 shadow-sm">
      <form id="formNuevoProducto" method="POST" enctype="multipart/form-data" action="{{ route('productos.store') }}">
        <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
          <h5 class="modal-title" id="modalProductoLabel">
            <i class="bi bi-box-seam me-2"></i> <span id="modalProductoTitulo">Crear Producto</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body" style="background-color: #f9fbfd;">
          @csrf
          <input type="hidden" name="producto_id" id="producto_id">

          <div class="mb-3">
            <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
            <input type="text" name="codigo" id="codigo" class="form-control" required style="border-color: #7CB9E6;" placeholder="Ej. PROD-001">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="nombre" class="form-control" required style="border-color: #7CB9E6;" placeholder="Nombre del producto">
          </div>

          <div class="mb-3">
            <label class="form-label">Presentación</label>
            <input type="text" name="presentacion" id="presentacion" class="form-control" placeholder="Ej. Caja, Bolsa, Rollo">
          </div>

          <div class="mb-3">
            <label class="form-label">Unidad</label>
            <input type="text" name="unidad" id="unidad" class="form-control" placeholder="Ej. cm, ml, unidades">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Cliente</label>
            <select name="producto_cliente" id="producto_cliente" class="form-select">
              <option value="">-- Seleccionar cliente --</option>
              @foreach($clientes as $cliente)
              <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
              @endforeach
            </select>
          </div>


          @hasanyrole('logistica|administrador')
          <div class="mb-3">
            <label class="form-label fw-semibold">Precio <span class="text-danger">*</span></label>
            <input type="number" name="precio" id="precio" step="0.01" class="form-control" required placeholder="Ej. 19.99">
          </div>
          @endhasanyrole

          <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
            <div id="previewImagen" class="mt-2"></div>
          </div>
        </div>

        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="submit" class="btn text-white" style="background-color: #0578BE;">
            <i class="bi bi-check-circle me-1"></i> <span id="btnGuardarTexto">Guardar</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
    