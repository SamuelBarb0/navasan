@props([
    'tipo'  => 'suaje',  // suaje | laminado | empalmado
    'delay' => 7000,
])

@php
    use Illuminate\Support\Facades\Cache;
    $mensaje = Cache::get("toast_{$tipo}_desfase_global");
@endphp

@if($mensaje)
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
  <div class="toast align-items-center bg-danger text-white border-0"
       role="alert" aria-live="assertive" aria-atomic="true"
       data-bs-delay="{{ $delay }}" id="toast-{{ $tipo }}-desfase">
    <div class="d-flex">
      <div class="toast-body">{!! $mensaje !!}</div>
      <form method="POST" action="{{ route('toasts.clear', ['tipo' => $tipo, 'scope' => 'desfase']) }}">
        @csrf
        <button type="submit" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('toast-{{ $tipo }}-desfase');
  if (el && window.bootstrap?.Toast) new bootstrap.Toast(el).show();
});
</script>
@endif
