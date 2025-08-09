<!-- Modal: Editar cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true"
     data-update-url-template="{{ route('clientes.update', ['cliente' => ':id']) }}">
  <div class="modal-dialog">
    <form id="formEditarCliente" class="modal-content" method="POST" action="#">
      @csrf
      @method('PATCH')

      <div class="modal-header bg-[#16509D] text-white">
        <h5 class="modal-title" id="modalEditarClienteLabel">Editar Cliente</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="edit_id">

        <div class="mb-3">
          <label for="edit_nombre" class="form-label text-[#16509D]">Nombre</label>
          <input type="text" name="nombre" id="edit_nombre" class="form-control border-[#9EA1A2]" required>
        </div>

        <div class="mb-3">
          <label for="edit_nit" class="form-label text-[#16509D]">RFC</label>
          <input type="text" name="nit" id="edit_nit" class="form-control border-[#9EA1A2]">
        </div>

        <div class="mb-3">
          <label for="edit_telefono" class="form-label text-[#16509D]">Teléfono</label>
          <input type="text" name="telefono" id="edit_telefono" class="form-control border-[#9EA1A2]">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn text-white" style="background-color: #0578BE;">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalEditarCliente');
  const form  = document.getElementById('formEditarCliente');

  modal.addEventListener('show.bs.modal', (event) => {
    const button   = event.relatedTarget;
    const id       = button.getAttribute('data-id');
    const nombre   = button.getAttribute('data-nombre') || '';
    const nit      = button.getAttribute('data-nit') || '';
    const telefono = button.getAttribute('data-telefono') || '';

    // Rellenar campos
    document.getElementById('edit_id').value       = id;
    document.getElementById('edit_nombre').value   = nombre;
    document.getElementById('edit_nit').value      = nit;
    document.getElementById('edit_telefono').value = telefono;

    // Set acción del form (PATCH)
    const template = modal.getAttribute('data-update-url-template'); // .../clientes/:id
    const action   = template.replace(':id', id);
    form.setAttribute('action', action);
  });
});
</script>
