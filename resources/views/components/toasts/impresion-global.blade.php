@php
  $toastImpGlobal  = Cache::get('toast_impresion_global');
  $toastImpDesfase = Cache::get('toast_impresion_desfase_global');
@endphp

@if($toastImpGlobal || $toastImpDesfase)
  <div class="position-fixed top-0 end-0 p-3" style="z-index:1055">
    @if($toastImpGlobal)
      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="d-flex">
          <div class="toast-body">{!! $toastImpGlobal !!}</div>
          <form method="POST" action="{{ route('toasts.clear', ['tipo'=>'impresion','scope'=>'global']) }}">
            @csrf
            <button type="submit" class="btn-close btn-close-white me-2 m-auto" aria-label="Cerrar"></button>
          </form>
        </div>
      </div>
    @endif

    @if($toastImpDesfase)
      <div class="toast align-items-center text-bg-warning border-0 mb-2"
           role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="d-flex">
          <div class="toast-body">{!! $toastImpDesfase !!}</div>
          <form method="POST" action="{{ route('toasts.clear', ['tipo'=>'impresion','scope'=>'desfase']) }}">
            @csrf
            <button type="submit" class="btn-close btn-close-white me-2 m-auto" aria-label="Cerrar"></button>
          </form>
        </div>
      </div>
    @endif
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!(window.bootstrap && bootstrap.Toast)) return;
      document.querySelectorAll('.toast:not(.bs-initialized)').forEach(function (el) {
        el.classList.add('bs-initialized');
        const inst = bootstrap.Toast.getOrCreateInstance(el, { autohide: false });
        inst.show();
        el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
      });
    });
  </script>

@endif
