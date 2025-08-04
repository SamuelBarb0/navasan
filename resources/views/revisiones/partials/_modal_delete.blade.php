@foreach ($revisiones as $revision)
<div class="modal fade" id="modalEliminarRevision{{ $revision->id }}" tabindex="-1" aria-labelledby="modalEliminarRevisionLabel{{ $revision->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('revisiones.destroy', $revision->id) }}">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header" style="background-color: #a32020; color: white;">
                    <h5 class="modal-title" id="modalEliminarRevisionLabel{{ $revision->id }}">Eliminar Revisión</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body bg-light text-center">
                    <p class="mb-0">¿Estás seguro de que deseas eliminar esta revisión?</p>
                    <p class="text-danger fw-bold">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
