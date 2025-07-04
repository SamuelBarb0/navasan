<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 shadow-sm">
      <form id="formNuevoProducto">
        <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
          <h5 class="modal-title" id="modalProductoLabel">
            <i class="bi bi-box-seam me-2"></i> Crear Producto
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body" style="background-color: #f9fbfd;">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                <input type="text" name="codigo" class="form-control" required style="border-color: #7CB9E6;" placeholder="Ej. PROD-001">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required style="border-color: #7CB9E6;" placeholder="Nombre del producto">
            </div>

            <div class="mb-3">
                <label class="form-label">Presentación</label>
                <input type="text" name="presentacion" class="form-control" placeholder="Ej. Caja, Bolsa, Rollo">
            </div>

            <div class="mb-3">
                <label class="form-label">Unidad</label>
                <input type="text" name="unidad" class="form-control" placeholder="Ej. cm, ml, unidades">
            </div>
        </div>

        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="submit" class="btn text-white" style="background-color: #0578BE;">
            <i class="bi bi-check-circle me-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
