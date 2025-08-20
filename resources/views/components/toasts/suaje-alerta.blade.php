@props([
    'tipo'  => 'suaje',  // suaje | laminado | empalmado
    'delay' => 5000,
])

@php
    use Illuminate\Support\Facades\Cache;
    $mensaje = Cache::get("toast_{$tipo}_global");
@endphp

@if($mensaje)
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
  <div class="toast align-items-center bg-warning text-dark border-0"
       role="alert" aria-live="assertive" aria-atomic="true"
       data-bs-delay="{{ $delay }}" id="toast-{{ $tipo }}-global">
    <div class="d-flex">
      <div class="toast-body">{!! $mensaje !!}</div>
      <form method="POST" action="{{ route('toasts.clear', ['tipo' => $tipo, 'scope' => 'global']) }}">
        @csrf
        <button type="submit" class="btn-close me-2 m-auto" aria-label="Close"></button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('toast-{{ $tipo }}-global');
  if (el && window.bootstrap?.Toast) new bootstrap.Toast(el).show();
});
</script>
@endif
