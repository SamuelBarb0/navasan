<!-- Modal Registrar Impresión -->
<div class="modal fade" id="modalRegistrarImpresion" tabindex="-1" aria-labelledby="modalRegistrarImpresionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('impresiones.store') }}">
            @csrf
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title" id="modalRegistrarImpresionLabel">Registrar Impresión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @foreach(\App\Models\OrdenProduccion::latest()->take(10)->get() as $orden)
                                <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de impresión</label>
                        <select name="tipo_impresion" class="form-select" required>
                            <option value="">Seleccione tipo</option>
                            <option value="Offset">Offset</option>
                            <option value="Xerox">Xerox</option>
                            <option value="Serigrafía">Serigrafía</option>
                            <option value="Hot Stamping">Hot Stamping</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Máquina utilizada</label>
                        <select id="maquinaSelectCrear" class="form-select">
                            <option value="">Seleccione</option>
                            <option value="MO">MO</option>
                            <option value="GTO">GTO</option>
                            <option value="otro">Otro</option>
                        </select>

                        <div class="mt-2 d-none" id="otraMaquinaDivCrear">
                            <input type="text" class="form-control" id="otraMaquinaInputCrear" placeholder="Especifique la máquina">
                        </div>

                        <!-- Campo oculto real -->
                        <input type="hidden" name="maquina" id="maquinaHiddenCrear">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad de pliegos impresos</label>
                        <input type="number" name="cantidad_pliegos" class="form-control" required min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Inicio de impresión</label>
                        <input type="datetime-local" name="inicio_impresion" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fin de impresión</label>
                        <input type="datetime-local" name="fin_impresion" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="espera">🟠 En espera</option>
                            <option value="proceso">🔵 En proceso</option>
                            <option value="completado">🟢 Completado</option>
                            <option value="rechazado">🔴 Rechazado</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal de creación
        const selectCrear = document.getElementById('maquinaSelectCrear');
        const inputCrear = document.getElementById('otraMaquinaInputCrear');
        const divCrear = document.getElementById('otraMaquinaDivCrear');
        const hiddenCrear = document.getElementById('maquinaHiddenCrear');

        function actualizarCrear() {
            if (selectCrear.value === 'otro') {
                divCrear.classList.remove('d-none');
                inputCrear.required = true;
                hiddenCrear.value = inputCrear.value;
            } else {
                divCrear.classList.add('d-none');
                inputCrear.required = false;
                inputCrear.value = '';
                hiddenCrear.value = selectCrear.value;
            }
        }

        selectCrear.addEventListener('change', actualizarCrear);
        inputCrear.addEventListener('input', () => {
            hiddenCrear.value = inputCrear.value;
        });

        actualizarCrear();
    });
</script>