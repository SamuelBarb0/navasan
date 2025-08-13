<div class="modal fade" id="modalRegistrarRevision" tabindex="-1" aria-labelledby="modalRegistrarRevisionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('revisiones.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background-color: #16509D; color: white;">
                    <h5 class="modal-title" id="modalRegistrarRevisionLabel">Registrar Revisión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body" style="background-color: #f8f9fa;">
                    <!-- Orden de Producción -->
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach($ordenes as $orden)
                                <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <!-- Revisores dinámicos -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-primary m-0">Revisores</h6>
                        <button type="button" id="btnAddRevisor" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Agregar revisor
                        </button>
                    </div>

                    <div id="revisoresContainer"></div>

                    <!-- Template oculto para clonar filas -->
                    <template id="revisorRowTemplate">
                        <div class="row border rounded p-2 mb-3 bg-white shadow-sm align-items-end revisor-row">
                            <div class="col-md-4">
                                <label class="form-label">Nombre del revisor</label>
                                <input type="text" class="form-control revisor-nombre" placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cantidad revisada</label>
                                <input type="number" class="form-control revisor-cantidad" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Comentario</label>
                                <input type="text" class="form-control revisor-comentarios" placeholder="Observaciones (opcional)">
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="button" class="btn btn-outline-danger mt-md-4 btnRemoveRevisor" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Tipo de revisión -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de revisión</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione tipo</option>
                            <option value="correcta">✅ Correcta</option>
                            <option value="defectos">⚠️ Defectos pero sirve</option>
                            <option value="apartada">🟠 Pausada a la espera de aprobación</option>
                            <option value="rechazada">❌ Rechazada</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn" style="background-color: #0578BE; color: #fff;">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Script para manejo dinámico de revisores --}}
<script>
(function () {
  const modal     = document.getElementById('modalRegistrarRevision');
  const container = document.getElementById('revisoresContainer');
  const template  = document.getElementById('revisorRowTemplate');
  const addBtn    = document.getElementById('btnAddRevisor');
  const form      = modal ? modal.querySelector('form') : null;

  // Crea una fila y la agrega al contenedor
  function addRevisorRow(data = {}) {
    const fragment = template.content.cloneNode(true);
    const row  = fragment.querySelector('.revisor-row');

    // Pre-cargar valores si se pasan (opcional)
    row.querySelector('.revisor-nombre').value      = data.revisado_por || '';
    row.querySelector('.revisor-cantidad').value    = data.cantidad || '';
    row.querySelector('.revisor-comentarios').value = data.comentarios || '';

    // Eliminar fila (impide borrar si es la única)
    const removeBtn = row.querySelector('.btnRemoveRevisor');
    removeBtn.addEventListener('click', () => {
      const rows = container.querySelectorAll('.revisor-row');
      if (rows.length <= 1) return; // bloquear borrado del último
      row.remove();
      reindexInputs();
      updateDeleteButtonsState();
    });

    container.appendChild(fragment);
    reindexInputs();
    updateDeleteButtonsState();
  }

  // Reindexa los name="revisores[i][campo]" según el orden actual
  function reindexInputs() {
    const rows = container.querySelectorAll('.revisor-row');
    rows.forEach((row, index) => {
      row.querySelector('.revisor-nombre').setAttribute('name', `revisores[${index}][revisado_por]`);
      row.querySelector('.revisor-cantidad').setAttribute('name', `revisores[${index}][cantidad]`);
      row.querySelector('.revisor-comentarios').setAttribute('name', `revisores[${index}][comentarios]`);
    });
  }

  // Habilita/deshabilita el botón eliminar según la cantidad de filas
  function updateDeleteButtonsState() {
    const rows = container.querySelectorAll('.revisor-row');
    const onlyOne = rows.length <= 1;
    rows.forEach((row) => {
      const btn = row.querySelector('.btnRemoveRevisor');
      btn.disabled = onlyOne;
      btn.title = onlyOne ? 'Debe haber al menos un revisor' : 'Eliminar';
      // opcional: estilo visual cuando está deshabilitado
      if (onlyOne) {
        btn.classList.add('disabled');
      } else {
        btn.classList.remove('disabled');
      }
    });
  }

  // Agregar fila al hacer click
  if (addBtn) addBtn.addEventListener('click', () => addRevisorRow());

  // Inicia con 1 fila por defecto
  addRevisorRow();

  // Limpieza/validación ligera al enviar (opcional)
  if (form) {
    form.addEventListener('submit', (e) => {
      const rows = container.querySelectorAll('.revisor-row');

      // impedir envío sin filas (defensivo)
      if (rows.length === 0) {
        e.preventDefault();
        alert('Agrega al menos un revisor.');
        return;
      }

      // eliminar filas totalmente vacías antes de enviar
      let removed = false;
      rows.forEach((row) => {
        const nombre = row.querySelector('.revisor-nombre').value.trim();
        const cantidad = row.querySelector('.revisor-cantidad').value.trim();
        const comentarios = row.querySelector('.revisor-comentarios').value.trim();
        const allEmpty = !nombre && !cantidad && !comentarios;
        if (allEmpty && rows.length > 1) {
          row.remove();
          removed = true;
        }
      });

      if (removed) {
        reindexInputs();
        updateDeleteButtonsState();
      }
    });
  }
})();
</script>
