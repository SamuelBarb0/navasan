@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4 border-0">
    <div class="card-header text-white rounded-top-4 py-3 px-4" style="background-color: #16509D;">
      <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventario de Etiquetas Excedentes</h5>
    </div>

    <div class="card-body bg-white px-4 py-4">

      @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-shield-exclamation me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      @endif

      {{-- Formulario de Registro --}}
      <form method="POST" action="{{ route('inventario-etiquetas.store') }}" class="mb-4" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
          {{-- Orden --}}
          <div class="col-md-4">
            <label class="form-label">Orden de Producción (opcional)</label>
            <select name="orden_id" class="form-select" id="ordenSelect">
              <option value="">Sin orden</option>
              @foreach($ordenes as $orden)
              <option value="{{ $orden->id }}">#{{ $orden->numero_orden }} - {{ $orden->cliente->nombre }}</option>
              @endforeach
            </select>
          </div>

          {{-- Cliente (independiente de si hay orden) --}}
          <div class="col-md-4">
            <label class="form-label">Cliente (opcional)</label>
            <select name="cliente_id" id="clienteSelect" class="form-select">
              <option value="">Sin cliente</option>
              @foreach(($clientes ?? []) as $c)
              <option value="{{ $c->id }}" @selected(old('cliente_id')==$c->id)>{{ $c->nombre }}</option>
              @endforeach
            </select>
          </div>

          {{-- Cantidad --}}
          <div class="col-md-4">
            <label class="form-label">Cantidad Excedente</label>
            <input type="number" name="cantidad" class="form-control" required min="1" value="{{ old('cantidad') }}">
          </div>

          {{-- Producto libre --}}
          <div class="col-md-4" id="grupoProductoLibre" style="display: block;">
            <label class="form-label">Producto (sin orden)</label>
            <select name="producto_id" id="productoLibreSelect" class="form-select" disabled>
              <option value="">Seleccione un cliente primero</option>
            </select>
          </div>

          {{-- Producto de orden --}}
          <div class="col-md-4" id="grupoItemOrden" style="display: none;">
            <label class="form-label">Producto (de orden)</label>
            <select name="item_orden_id" id="itemOrdenSelect" class="form-select">
              <option value="">Seleccione un producto</option>
            </select>
          </div>

          {{-- Fecha --}}
          <div class="col-md-4">
            <label class="form-label">Fecha programada entrega</label>
            <input type="date" name="fecha_programada" class="form-control" value="{{ old('fecha_programada') }}">
          </div>

          {{-- Observaciones --}}
          <div class="col-md-8">
            <label class="form-label">Observaciones</label>
            <input type="text" name="observaciones" class="form-control" maxlength="1000" value="{{ old('observaciones') }}">
          </div>

          {{-- Imagen única + preview --}}
          <div class="col-md-4">
            <label class="form-label">Imagen (opcional, 1)</label>
            <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
            @php
            $placeholder = asset('images/no-image.png');
            @endphp
            <div class="mt-2">
              <img id="previewImg" src="{{ $placeholder }}" alt="Preview" style="max-height: 140px; border:1px solid #eee; border-radius:8px;">
            </div>
          </div>

          {{-- Botón --}}
          <div class="col-md-12 text-end mt-2">
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-save me-1"></i> Guardar
            </button>
          </div>
        </div>
      </form>

      <hr>
      {{-- Tabla de registros --}}
      <h6 class="text-secondary mb-3">Etiquetas registradas:</h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Imagen</th>
              <th>Orden</th>
              <th>Código</th> {{-- NUEVA COLUMNA --}}
              <th>Producto</th>
              <th>Cliente</th>
              <th>Cantidad</th>
              <th>Observaciones</th>
              <th>Programada para</th>
              <th>Estado</th>
              <th>Creado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($inventarios as $item)
            @php
            // Determinar URL de imagen (preferencia: imagen del item > imagen del producto > placeholder)
            $imgUrl = $item->imagen
            ? asset($item->imagen)
            : ($item->producto && $item->producto->imagen
            ? asset($item->producto->imagen)
            : $placeholder);

            // Nombre del producto (según origen)
            $nombreProducto = $item->itemOrden?->nombre
            ?? $item->producto?->nombre
            ?? '—';

            // CÓDIGO del producto usando relación / accessor
            // Prioridad: producto->codigo_preferido > producto->codigo > itemOrden->codigo
            $codigoProducto = $item->producto?->codigo_preferido
            ?? $item->producto?->codigo
            ?? $item->itemOrden?->codigo
            ?? '—';
            @endphp
            <tr>
              <td>
                <img src="{{ $imgUrl }}" alt="img"
                  style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #eee;">
              </td>
              <td>
                @if($item->orden)
                <strong>#{{ $item->orden->numero_orden }}</strong>
                @else
                <span class="text-muted">—</span>
                @endif
              </td>
              <td>
                {{ $codigoProducto }}
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="{{ $imgUrl }}" alt="img"
                    style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid #ddd;margin-right:8px;">
                  <span>{{ $nombreProducto }}</span>
                </div>
              </td>
              <td>
                {{ $item->orden->cliente->nombre ?? ($item->cliente->nombre ?? '—') }}
              </td>
              <td>{{ $item->cantidad }}</td>
              <td>{{ $item->observaciones ?? '—' }}</td>
              <td>
                @if($item->fecha_programada)
                <span class="badge bg-warning text-dark">{{ \Carbon\Carbon::parse($item->fecha_programada)->format('Y-m-d') }}</span>
                @else
                <span class="text-muted">No definida</span>
                @endif
              </td>
              <td>
                @php
                $badgeClass = match($item->estado) {
                'liberado' => 'bg-success',
                'stock' => 'bg-info text-dark',
                'pendiente' => 'bg-secondary',
                default => 'bg-light text-muted'
                };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ ucfirst($item->estado ?? 'sin estado') }}</span>
              </td>
              <td>{{ $item->created_at->format('d/m/Y') }}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $item->id }}">
                  <i class="bi bi-pencil-square"></i> Editar
                </button>
                <form id="form-eliminar-{{ $item->id }}" action="{{ route('inventario-etiquetas.destroy', $item) }}" method="POST" style="display: none;">
                  @csrf
                  @method('DELETE')
                </form>
                <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminacionEtiqueta({{ $item->id }}, '{{ $item->id }}')">
                  <i class="bi bi-trash-fill"></i> Eliminar
                </button>
                @include('partials.modal-editar', ['etiqueta' => $item])
              </td>
            </tr>
            @empty
            <tr>
              {{-- Aumenta en 1 por la nueva columna --}}
              <td colspan="11" class="text-center text-muted">No hay registros aún.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  /** Confirmación de eliminación */
  function confirmarEliminacionEtiqueta(id, nombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar el registro #${nombre}? Esta acción no se puede deshacer.`)) {
      document.getElementById(`form-eliminar-${id}`).submit();
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    /* --- Elementos --- */
    const ordenSelect = document.getElementById('ordenSelect');
    const grupoItemOrden = document.getElementById('grupoItemOrden');
    const grupoProductoLibre = document.getElementById('grupoProductoLibre');
    const itemOrdenSelect = document.getElementById('itemOrdenSelect');

    const clienteSelect = document.getElementById('clienteSelect');
    const productoLibreSelect = document.getElementById('productoLibreSelect');

    const fileInput = document.getElementById('imagen');
    const previewImg = document.getElementById('previewImg');
    const placeholder = "{{ asset('images/no-image.png') }}";

    /* --- Helpers --- */
    function setProductoLibreDisabled(state, placeholderText) {
      if (!productoLibreSelect) return;
      productoLibreSelect.disabled = state;
      productoLibreSelect.innerHTML = `<option value="">${placeholderText}</option>`;
    }

    function updatePreviewFromSelection() {
      if (!previewImg) return;
      if (fileInput?.files?.length) return;
      const opt = productoLibreSelect?.options?.[productoLibreSelect.selectedIndex];
      const url = opt?.getAttribute?.('data-img');
      previewImg.src = url || placeholder;
    }

    function attachFilePreviewListener() {
      if (!fileInput || !previewImg) return;
      fileInput.addEventListener('change', () => {
        if (!fileInput.files.length) {
          updatePreviewFromSelection();
          return;
        }
        const reader = new FileReader();
        reader.onload = e => {
          previewImg.src = e.target.result;
        };
        reader.readAsDataURL(fileInput.files[0]);
      });
    }

    // Nueva función con nombre cambiado
    function inventarioPorCliente(clienteId) {
      if (!clienteId) {
        setProductoLibreDisabled(true, 'Seleccione un cliente primero');
        return;
      }
      setProductoLibreDisabled(true, 'Cargando productos…');

      fetch(`/clientes/${clienteId}/productos-json`)
        .then(res => res.json())
        .then(data => {
          let options = '<option value="">Seleccione un producto</option>';
          data.forEach(p => {
            options += `<option value="${p.id}" data-img="${p.imagen_url ?? ''}">${p.nombre}</option>`;
          });
          productoLibreSelect.innerHTML = options;
          productoLibreSelect.disabled = false;

          const oldProducto = @json(old('producto_id'));
          if (oldProducto) {
            productoLibreSelect.value = String(oldProducto);
          }
          updatePreviewFromSelection();
        })
        .catch(() => {
          setProductoLibreDisabled(true, 'Error al cargar productos');
        });
    }

    /* --- Listeners --- */
    ordenSelect?.addEventListener('change', function() {
      const ordenId = this.value;

      if (!ordenId) {
        grupoItemOrden.style.display = 'none';
        grupoProductoLibre.style.display = 'block';

        if (clienteSelect?.value) {
          inventarioPorCliente(clienteSelect.value);
        } else {
          setProductoLibreDisabled(true, 'Seleccione un cliente primero');
          updatePreviewFromSelection();
        }
        return;
      }

      grupoItemOrden.style.display = 'block';
      grupoProductoLibre.style.display = 'none';
      itemOrdenSelect.innerHTML = '<option value="">Cargando productos...</option>';

      fetch(`/ordenes/${ordenId}/items-json`)
        .then(res => res.json())
        .then(data => {
          let options = '<option value="">Seleccione un producto</option>';
          data.forEach(p => {
            options += `<option value="${p.id}">${p.nombre}</option>`;
          });
          itemOrdenSelect.innerHTML = options;
        })
        .catch(() => {
          itemOrdenSelect.innerHTML = '<option value="">Error al cargar</option>';
        });
    });

    clienteSelect?.addEventListener('change', () => {
      if (ordenSelect?.value) return;
      if (!clienteSelect.value) {
        setProductoLibreDisabled(true, 'Seleccione un cliente primero');
        updatePreviewFromSelection();
        return;
      }
      inventarioPorCliente(clienteSelect.value);
    });

    productoLibreSelect?.addEventListener('change', updatePreviewFromSelection);

    attachFilePreviewListener();

    /* --- Estado inicial --- */
    if (ordenSelect?.value) {
      grupoItemOrden.style.display = 'block';
      grupoProductoLibre.style.display = 'none';
    } else {
      grupoItemOrden.style.display = 'none';
      grupoProductoLibre.style.display = 'block';

      if (clienteSelect?.value) {
        inventarioPorCliente(clienteSelect.value);
      } else {
        setProductoLibreDisabled(true, 'Seleccione un cliente primero');
        updatePreviewFromSelection();
      }
    }
  });
</script>


@endsection