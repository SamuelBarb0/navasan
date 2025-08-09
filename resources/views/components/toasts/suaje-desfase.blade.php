@php
    $mensaje = \Illuminate\Support\Facades\Cache::get('toast_suaje_desfase_global');
@endphp

@if($mensaje)
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
  <div class="toast align-items-center bg-danger text-white border-0" id="toastSuajeDesfase"
       role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="7000">
    <div class="d-flex">
      <div class="toast-body">{!! $mensaje !!}</div>
      <form method="POST" action="{{ route('toasts.suaje.desfase.clear') }}">
        @csrf
        <button type="submit" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('toastSuajeDesfase');
  if (el) new bootstrap.Toast(el).show();
});
</script>
@endif
