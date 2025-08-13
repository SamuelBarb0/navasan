@php
    // Firma del contenido (si cambia la lista, será un toast "nuevo")
    $signature = $ordenes->isNotEmpty() ? substr(hash('sha256', $ordenes->join('|')), 0, 16) : null;
@endphp

@if($ordenes->isNotEmpty())
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastRevisionPendienteGlobal"
             class="toast align-items-center text-bg-warning border-0 shadow"
             role="alert" aria-live="assertive" aria-atomic="true"
             data-bs-autohide="true" data-bs-delay="5000"
             data-key="{{ $signature }}">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    ⚠️ Por favor revisar revisión de:
                    <ul class="mb-0">
                        @foreach($ordenes as $numero)
                            <li>#{{ $numero }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
      const el = document.getElementById('toastRevisionPendienteGlobal');
      if (!el) return;

      const key = el.dataset.key || 'global';
      const lsKey = `toastRevisionDismissed:${key}`;

      // Si ya fue descartado, no lo mostramos
      if (localStorage.getItem(lsKey) === '1') {
        el.remove();
        return;
      }

      const t = new bootstrap.Toast(el);

      // Al cerrarlo (por tiempo o click), marcar como descartado
      el.addEventListener('hidden.bs.toast', () => {
        localStorage.setItem(lsKey, '1');
      });

      t.show();
    });
    </script>
@endif
