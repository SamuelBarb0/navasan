@if($ordenes->isNotEmpty())
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastRevisionPendienteGlobal" class="toast align-items-center text-bg-warning border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    ⚠️ Por favor revisar revisión de:
                    <ul class="mb-0">
                        @foreach($ordenes as $numero)
                            <li>{{ $numero }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar" onclick="limpiarToastRevisionGlobal()"></button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('toastRevisionPendienteGlobal');
            if (el) new bootstrap.Toast(el, { delay: 5000 }).show();
        });
        function limpiarToastRevisionGlobal() {
            fetch('{{ route('revisiones.limpiar.toast') }}'); // Esta ruta debe limpiar la bandera de sesión
        }
    </script>
@endif
