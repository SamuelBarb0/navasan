@if($shouldShow && $ordenes->isNotEmpty())
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastRevisionPendienteGlobal" class="toast align-items-center text-bg-warning border-0 shadow"
             role="alert" aria-live="assertive" aria-atomic="true"
             data-bs-autohide="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    ⚠️ Por favor revisar revisión de:
                    <ul class="mb-0">
                        @foreach($ordenes as $numero)
                            <li>#{{ $numero }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
      const el = document.getElementById('toastRevisionPendienteGlobal');
      if (!el) return;
      const t = new bootstrap.Toast(el);
      el.addEventListener('hidden.bs.toast', () => {
        // opcional: si quieres asegurarte de limpiarlo del lado servidor
        fetch('{{ route('revisiones.limpiar.toast') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
      });
      t.show();
    });
    </script>
@endif
