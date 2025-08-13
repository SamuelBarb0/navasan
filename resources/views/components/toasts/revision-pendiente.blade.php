@php
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Arr;

    // Trae y normaliza lo que esté en caché
    $raw = Cache::get('toast_revision_ordenes', []);

    $ordenesToast = collect(Arr::wrap($raw))
        ->map(function ($o) {
            // Si ya es string o numérico, úsalo tal cual
            if (is_string($o) || is_numeric($o)) {
                return trim((string) $o);
            }
            // Si es array: busca claves comunes
            if (is_array($o)) {
                return trim((string) ($o['numero_orden'] ?? $o['numero'] ?? $o['nombre'] ?? ''));
            }
            // Si es objeto Eloquent u objeto genérico
            if (is_object($o)) {
                // Intenta propiedades más comunes
                $val = $o->numero_orden ?? $o->numero ?? $o->nombre ?? null;
                if ($val !== null) return trim((string) $val);
                // Intenta getAttribute si es modelo
                if (method_exists($o, 'getAttribute')) {
                    $val = $o->getAttribute('numero_orden') ?? $o->getAttribute('numero') ?? $o->getAttribute('nombre');
                    if ($val !== null) return trim((string) $val);
                }
            }
            return ''; // si no logró resolver
        })
        ->filter()     // quita vacíos
        ->unique()     // quita duplicados
        ->values();    // reindexa
@endphp

@if($ordenesToast->isNotEmpty())
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastRevisionPendienteGlobal" class="toast align-items-center text-bg-warning border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    ⚠️ Por favor revisar revisión de:
                    <ul class="mb-0">
                        @foreach($ordenesToast as $texto)
                            <li>{{ $texto }}</li>
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
            fetch('{{ route("revisiones.limpiar.toast") }}');
        }
    </script>
@endif
