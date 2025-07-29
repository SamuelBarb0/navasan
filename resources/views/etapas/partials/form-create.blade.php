<div class="modal fade" id="modalCrearEtapa" tabindex="-1" aria-labelledby="modalCrearEtapaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('etapas.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCrearEtapaLabel">Crear Nueva Etapa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="usuario_id" class="form-label">Usuario Asignado</label>
                    <select name="usuario_id" class="form-select">
                        <option value="">-- Sin asignar --</option>
                        @foreach(\App\Models\User::all() as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
