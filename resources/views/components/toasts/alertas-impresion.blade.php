@if($ordenesSinFin->isNotEmpty() || $diferencias->isNotEmpty())
  <div class="toast-auto-show position-fixed top-0 end-0 p-3" style="z-index: 1055">

    {{-- FALTA FIN --}}
    @foreach ($ordenesSinFin as $index => $o)
      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-delay="6000" id="toastFin{{ $index }}">
        <div class="d-flex">
          <div class="toast-body">
            ⚠️ La orden <strong>#{{ $o->numero }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
          </div>

          {{-- Limpieza server-side (preferente por sig) --}}
          <form method="POST" action="{{ route('toasts.impresion.fin.clear') }}">
            @csrf
            <input type="hidden" name="sig" value="{{ $o->sig }}">
            {{-- Fallback legacy por número --}}
            <input type="hidden" name="numero" value="{{ $o->numero }}">
            <button type="submit"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"></button>
          </form>
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

          {{-- Limpieza server-side (preferente por sig) --}}
          <form method="POST" action="{{ route('toasts.impresion.diff.clear') }}">
            @csrf
            <input type="hidden" name="sig" value="{{ $d->sig }}">
            {{-- Fallback legacy por ID de impresión --}}
            <input type="hidden" name="impresion_id" value="{{ $d->id }}">
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
      document.querySelectorAll('.toast-auto-show .toast:not(.bs-initialized)').forEach(function (el) {
        el.classList.add('bs-initialized');
        bootstrap.Toast.getOrCreateInstance(el).show();
      });
    });
  </script>
@endif
