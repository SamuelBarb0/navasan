<!-- Modal Editar Impresión -->
<div class="modal fade" id="modalEditarImpresion{{ $item->id }}" tabindex="-1" aria-labelledby="editarImpresionLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('impresiones.update', $item->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title" id="editarImpresionLabel{{ $item->id }}">Editar Impresión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <select name="orden_id" class="form-select" required>
                            @foreach(\App\Models\OrdenProduccion::all() as $orden)
                                <option value="{{ $orden->id }}" {{ $orden->id == $item->orden_id ? 'selected' : '' }}>
                                    {{ $orden->numero_orden }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Impresión</label>
                        <input type="text" name="tipo_impresion" class="form-control" value="{{ $item->tipo_impresion }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Máquina</label>
                        <select class="form-select maquina-select" data-id="{{ $item->id }}">
                            <option value="">Seleccione</option>
                            <option value="MO" {{ $item->maquina == 'MO' ? 'selected' : '' }}>MO</option>
                            <option value="GTO" {{ $item->maquina == 'GTO' ? 'selected' : '' }}>GTO</option>
                            <option value="otro" {{ !in_array($item->maquina, ['MO', 'GTO']) ? 'selected' : '' }}>Otro</option>
                        </select>

                        <div class="mt-2 {{ !in_array($item->maquina, ['MO', 'GTO']) ? '' : 'd-none' }}" id="otraMaquinaDiv{{ $item->id }}">
                            <input type="text" class="form-control" id="otraMaquinaInput{{ $item->id }}"
                                   placeholder="Especifique la máquina" value="{{ !in_array($item->maquina, ['MO', 'GTO']) ? $item->maquina : '' }}">
                        </div>

                        <!-- Este input es el que se envía realmente -->
                        <input type="hidden" name="maquina" id="maquinaHidden{{ $item->id }}" value="{{ $item->maquina }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad de pliegos</label>
                        <input type="number" name="cantidad_pliegos" class="form-control" value="{{ $item->cantidad_pliegos }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Inicio</label>
                        <input type="datetime-local" name="inicio_impresion" class="form-control"
                               value="{{ \Carbon\Carbon::parse($item->inicio_impresion)->format('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fin</label>
                        <input type="datetime-local" name="fin_impresion" class="form-control"
                               value="{{ \Carbon\Carbon::parse($item->fin_impresion)->format('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="espera" {{ $item->estado == 'espera' ? 'selected' : '' }}>🟠 En espera</option>
                            <option value="proceso" {{ $item->estado == 'proceso' ? 'selected' : '' }}>🔵 En proceso</option>
                            <option value="completado" {{ $item->estado == 'completado' ? 'selected' : '' }}>🟢 Completado</option>
                            <option value="rechazado" {{ $item->estado == 'rechazado' ? 'selected' : '' }}>🔴 Rechazado</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.maquina-select').forEach(select => {
            const id = select.dataset.id;
            const otraDiv = document.getElementById('otraMaquinaDiv' + id);
            const otraInput = document.getElementById('otraMaquinaInput' + id);
            const hiddenInput = document.getElementById('maquinaHidden' + id);

            function actualizarValor() {
                if (select.value === 'otro') {
                    otraDiv.classList.remove('d-none');
                    otraInput.required = true;
                    hiddenInput.value = otraInput.value;
                } else {
                    otraDiv.classList.add('d-none');
                    otraInput.required = false;
                    otraInput.value = '';
                    hiddenInput.value = select.value;
                }
            }

            select.addEventListener('change', actualizarValor);
            otraInput.addEventListener('input', () => {
                hiddenInput.value = otraInput.value;
            });

            actualizarValor(); // Inicializar al cargar
        });
    });
</script>