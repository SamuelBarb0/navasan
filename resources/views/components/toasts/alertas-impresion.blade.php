@if($ordenesSinFin->isNotEmpty() || $diferencias->isNotEmpty())
  <div class="toast-auto-show position-fixed top-0 end-0 p-3" style="z-index:1055">

    {{-- FALTA FIN --}}
    @foreach ($ordenesSinFin as $index => $o)
      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-delay="6000" id="toastFin{{ $index }}">
        <div class="d-flex">
          <div class="toast-body">
            ⚠️ La orden <strong>#{{ $o->numero }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
          </div>

          {{-- POST por fetch con firma (sig) + fallback (numero) --}}
          <button type="button"
                  class="btn-close btn-close-white me-2 m-auto"
                  aria-label="Cerrar"
                  data-clear-url="{{ route('toasts.impresion.fin.clear') }}"
                  data-payload='@json(["sig" => $o->sig, "numero" => $o->numero])'
                  onclick="window.__clearToast(this)"
                  data-bs-dismiss="toast"></button>
        </div>
      </div>
    @endforeach

    {{-- DIFERENCIAS DE PLIEGOS --}}
    @foreach ($diferencias as $index => $d)
      @php
        $orden = $d->numero ?? 'N/A';
        $msg = $d->impresos > $d->solicitados
          ? "⚠️ La orden <strong>#{$orden}</strong> tiene más pliegos impresos que los solicitados."
          : "⚠️ La orden <strong>#{$orden}</strong> tiene menos pliegos impresos que los solicitados.";
      @endphp

      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-delay="6000" id="toastPliegos{{ $index }}">
        <div class="d-flex">
          <div class="toast-body">{!! $msg !!}</div>

          {{-- POST por fetch con firma (sig) + fallback (impresion_id) --}}
          <button type="button"
                  class="btn-close btn-close-white me-2 m-auto"
                  aria-label="Cerrar"
                  data-clear-url="{{ route('toasts.impresion.diff.clear') }}"
                  data-payload='@json(["sig" => $d->sig, "impresion_id" => $d->id])'
                  onclick="window.__clearToast(this)"
                  data-bs-dismiss="toast"></button>
        </div>
      </div>
    @endforeach

  </div>

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
      document.querySelectorAll('.toast-auto-show .toast:not(.bs-initialized)').forEach(function (el) {
        el.classList.add('bs-initialized');
        bootstrap.Toast.getOrCreateInstance(el).show();
      });
    });
  })();
  </script>
@endif
