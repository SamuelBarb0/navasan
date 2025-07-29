<div class="modal fade" id="modalEditarEtapa{{ $etapa->id }}" tabindex="-1" aria-labelledby="modalEditarEtapaLabel{{ $etapa->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('etapas.update', $etapa) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalEditarEtapaLabel{{ $etapa->id }}">Editar Etapa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $etapa->nombre }}" required>
                </div>
                <div class="mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-control" value="{{ $etapa->orden }}">
                </div>
                <div class="mb-3">
                    <label for="usuario_id" class="form-label">Usuario Asignado</label>
                    <select name="usuario_id" class="form-select">
                        <option value="">-- Sin asignar --</option>
                        @foreach(\App\Models\User::all() as $usuario)
                            <option value="{{ $usuario->id }}" @if($etapa->usuario_id == $usuario->id) selected @endif>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-secondary">Actualizar</button>
            </div>
        </form>
    </div>
</div>
