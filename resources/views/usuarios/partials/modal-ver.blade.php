@foreach($usuarios as $usuario)
<div class="modal fade" id="modalVerUsuario{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalles de usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
                <p><strong>Email:</strong> {{ $usuario->email }}</p>
                <p><strong>Rol:</strong>
                    @forelse($usuario->getRoleNames() as $rol)
                        <span class="badge bg-primary">{{ $rol }}</span>
                    @empty
                        <span class="text-muted">Sin rol</span>
                    @endforelse
                </p>
            </div>
        </div>
    </div>
</div>
@endforeach
