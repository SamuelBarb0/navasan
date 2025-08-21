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

          {{-- Botón que hace POST por fetch (sin <form>) --}}
          <button type="button"
                  class="btn-close btn-close-white me-2 m-auto"
                  aria-label="Cerrar"
                  data-clear-url="{{ route('toasts.impresion.fin.clear') }}"
                  data-payload='@json(["numero" => $numeroOrden])'
                  onclick="window.__clearToast(this)"
                  data-bs-dismiss="toast"></button>
        </div>
      </div>
    @endforeach
  </div>

  @push('scripts')
  <script>
  (function(){
    if (!window.__clearToast) {
      window.__clearToast = async function(btn) {
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
        } catch(e) {
          console.warn('No se pudo limpiar el toast en servidor', e);
        }
      };
    }

    document.addEventListener('DOMContentLoaded', function () {
      if (!(window.bootstrap && bootstrap.Toast)) return;
      document.querySelectorAll('.toast').forEach(function (el) {
        bootstrap.Toast.getOrCreateInstance(el).show();
      });
    });
  })();
  </script>
  @endpush
@endif
