@if(session()->has('mostrar_toast_revision') && count(session('mostrar_toast_revision')) > 0)
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
    <div id="toastRevisionGlobal"
         class="toast align-items-center text-bg-warning border-0 shadow"
         role="alert" aria-live="assertive" aria-atomic="true"
         data-bs-autohide="false">
      <div class="d-flex">
        <div class="toast-body fw-semibold">
          ⚠️ Por favor revisar revisión de:
          <ul class="mb-0">
            @foreach(session('mostrar_toast_revision') as $num)
              <li>#{{ $num }}</li>
            @endforeach
          </ul>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('toastRevisionGlobal');
    if (!el) return;

    const limpiarUrl = @json(route('revisiones.limpiar.toast'));
    const csrf = @json(csrf_token());
    let yaLimpio = false;

    function limpiarServidor() {
      if (yaLimpio) return; yaLimpio = true;
      fetch(limpiarUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).catch(() => {});
    }

    // Bootstrap 5
    try {
      const t = new bootstrap.Toast(el);
      // Cuando el toast termina de cerrarse (por la X), limpiamos
      el.addEventListener('hidden.bs.toast', limpiarServidor);
      // Por si quieres limpiar apenas se hace click en la X (opcional pero rápido)
      el.querySelector('.btn-close')?.addEventListener('click', limpiarServidor);

      t.show();
    } catch(e) {
      // En caso extremo sin bootstrap.js, al menos “simulamos” ocultarlo
      el.querySelector('.btn-close')?.addEventListener('click', () => {
        el.style.display = 'none';
        limpiarServidor();
      });
    }
  });
  </script>
@endif
