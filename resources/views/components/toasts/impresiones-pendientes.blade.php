@if($ordenes->isNotEmpty())
  <div class="position-fixed top-0 end-0 p-3" style="z-index:1055">
    @foreach ($ordenes as $index => $numeroOrden)
      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-delay="6000" id="toastOrden{{ $index }}">
        <div class="d-flex">
          <div class="toast-body">
            ⚠️ La orden <strong>#{{ $numeroOrden }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
            <a href="{{ route('impresiones.index') }}" class="ms-2 text-white text-decoration-underline">Ir a Impresiones</a>
          </div>

          {{-- Cierre vía fetch (marca por número) --}}
          <button type="button"
                  class="btn-close btn-close-white me-2 m-auto"
                  aria-label="Cerrar"
                  data-clear-url="{{ route('toasts.impresion.fin.clear') }}"
                  data-payload='@json(["numero" => $numeroOrden])'
                  onclick="window.__clearToast(this)"></button>
        </div>
      </div>
    @endforeach
  </div>
@endif

<script>
(function(){
  if (window.__toastInited) return;
  window.__toastInited = true;

  // Mostrar todos los .toast al cargar
  document.addEventListener('DOMContentLoaded', function () {
    if (!(window.bootstrap && bootstrap.Toast)) return;
    document.querySelectorAll('.toast:not(.bs-initialized)').forEach(function (el) {
      el.classList.add('bs-initialized');
      const inst = bootstrap.Toast.getOrCreateInstance(el, { autohide: false });
      inst.show();
      el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
    });
  });

  // Función global de cierre y limpieza en servidor
  window.__clearToast = async function(btn) {
    const toastEl = btn.closest('.toast');
    try {
      if (window.bootstrap && bootstrap.Toast) {
        bootstrap.Toast.getOrCreateInstance(toastEl).hide();
      } else {
        toastEl.style.display = 'none';
      }
    } catch (_) {
      toastEl.style.display = 'none';
    }

    try {
      const url = btn.getAttribute('data-clear-url');
      const payload = JSON.parse(btn.getAttribute('data-payload') || '{}');
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

      await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
    } catch (e) {
      console.warn('No se pudo limpiar el toast en servidor:', e);
    }
  };
})();
</script>

