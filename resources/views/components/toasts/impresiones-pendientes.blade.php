@if($ordenes->isNotEmpty())
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
    @foreach ($ordenes as $index => $numeroOrden)
      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-delay="6000" id="toastOrden{{ $index }}">
        <div class="d-flex">
          <div class="toast-body">
            ⚠️ La orden <strong>#{{ $numeroOrden }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
            <a href="{{ route('impresiones.index') }}" class="ms-2 text-white text-decoration-underline">
              Ir a Impresiones
            </a>
          </div>

          {{-- Cierre que limpia en servidor usando el fallback por número --}}
          <form method="POST" action="{{ route('toasts.impresion.fin.clear') }}">
            @csrf
            <input type="hidden" name="numero" value="{{ $numeroOrden }}">
            <button type="submit"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"></button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!(window.bootstrap && bootstrap.Toast)) return;
      document.querySelectorAll('#toastOrden0, #toastOrden1, .toast').forEach(function (el) {
        bootstrap.Toast.getOrCreateInstance(el).show();
      });
    });
  </script>
@endif
