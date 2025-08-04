@php
    use Illuminate\Support\Facades\Cache;
    $ordenesToast = Cache::get('toast_revision_ordenes', []);
@endphp

@if(is_array($ordenesToast) && count($ordenesToast))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastRevisionPendienteGlobal" class="toast align-items-center text-bg-warning border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    ⚠️ Por favor revisar revisión de:
                    <ul class="mb-0">
                        @foreach($ordenesToast as $orden)
                            <li>{{ $orden }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar" onclick="limpiarToastRevisionGlobal()"></button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            const toast = new bootstrap.Toast(document.getElementById('toastRevisionPendienteGlobal'), { delay: 5000 });
            toast.show();
        });

        function limpiarToastRevisionGlobal() {
            fetch('{{ route("revisiones.limpiar.toast") }}');
        }
    </script>
@endif
