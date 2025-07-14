@foreach($usuarios as $usuario)
<div class="modal fade" id="modalEditarUsuario{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Editar usuario: {{ $usuario->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" value="{{ $usuario->name }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $usuario->email }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Roles</label>
                        <select name="roles[]" class="form-select" multiple required>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->name }}" {{ $usuario->hasRole($rol->name) ? 'selected' : '' }}>
                                {{ $rol->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Usa Ctrl o Cmd para seleccionar varios</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-white">Actualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach