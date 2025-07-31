@if($ordenesSinFin->isNotEmpty() || $diferencias->isNotEmpty())
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
    {{-- Toasts por falta de fin --}}
    @foreach ($ordenesSinFin as $index => $numeroOrden)
        <div class="toast align-items-center text-bg-warning border-0 mb-2" role="alert"
             aria-live="assertive" aria-atomic="true" data-bs-delay="6000" id="toastFin{{ $index }}">
            <div class="d-flex">
                <div class="toast-body">
                    ⚠️ La orden <strong>#{{ $numeroOrden }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endforeach

    {{-- Toasts por diferencias de pliegos --}}
    @foreach ($diferencias as $index => $i)
        @php
            $orden = $i->orden->numero_orden ?? 'N/A';
            $msg = $i->cantidad_pliegos_impresos > $i->cantidad_pliegos
                ? "⚠️ La orden <strong>#{$orden}</strong> tiene más pliegos impresos que los solicitados."
                : "⚠️ La orden <strong>#{$orden}</strong> tiene menos pliegos impresos que los solicitados.";
        @endphp
        <div class="toast align-items-center text-bg-warning border-0 mb-2" role="alert"
             aria-live="assertive" aria-atomic="true" data-bs-delay="6000" id="toastPliegos{{ $index }}">
            <div class="d-flex">
                <div class="toast-body">{!! $msg !!}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toast').forEach(toastEl => {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    });
</script>
@endif
