@if($ordenes->isNotEmpty())
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
        @foreach ($ordenes as $index => $numeroOrden)
            <div class="toast align-items-center text-bg-warning border-0 mb-2 cursor-pointer"
                 role="alert"
                 aria-live="assertive"
                 aria-atomic="true"
                 data-bs-delay="6000"
                 id="toastOrden{{ $index }}"
                 onclick="window.location.href='{{ route('impresiones.index') }}'">
                <div class="d-flex">
                    <div class="toast-body">
                        ⚠️ La orden <strong>#{{ $numeroOrden }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastElements = document.querySelectorAll('.toast');
            toastElements.forEach(toastEl => {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        });
    </script>
@endif
