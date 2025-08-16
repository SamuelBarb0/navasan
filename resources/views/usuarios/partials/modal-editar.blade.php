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

                    {{-- 🔐 Contraseña (opcional) --}}
                    <div class="mb-3">
                        <label>Nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="8" placeholder="Dejar en blanco para no cambiar">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="const i=this.previousElementSibling; i.type=i.type==='password'?'text':'password'; this.textContent=this.textContent==='Mostrar'?'Ocultar':'Mostrar';">
                                Mostrar
                            </button>
                        </div>
                        <small class="text-muted">Déjala vacía si no quieres cambiarla.</small>
                    </div>

                    <div class="mb-3">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Repite la nueva contraseña">
                    </div>
                    {{-- /Contraseña --}}

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
