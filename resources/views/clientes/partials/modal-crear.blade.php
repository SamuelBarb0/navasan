<!-- Modal: Crear nuevo cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formNuevoCliente" class="modal-content" method="POST" action="{{ route('clientes.store') }}">
      @csrf
      <div class="modal-header bg-[#16509D] text-white">
        <h5 class="modal-title" id="modalNuevoClienteLabel">Nuevo Cliente</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="nombre" class="form-label text-[#16509D]">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control border-[#9EA1A2]" required>
        </div>
        <div class="mb-3">
            <label for="nit" class="form-label text-[#16509D]">RFC</label>
            <input type="text" name="nit" id="nit" class="form-control border-[#9EA1A2]">
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label text-[#16509D]">Teléfono</label>
            <input type="text" name="telefono" id="telefono" class="form-control border-[#9EA1A2]">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn text-white" style="background-color: #0578BE;">Guardar Cliente</button>
      </div>
    </form>
  </div>
</div>
