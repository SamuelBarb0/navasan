@extends('layouts.app')

@section('content')
<div class="container py-6">
    <div class="bg-white shadow rounded-lg overflow-hidden border border-[#9EA1A2]">
        <div class="bg-[#16509D] text-white px-4 py-3 flex justify-between items-center">
            <h2 class="text-lg font-semibold"><i class="bi bi-people-fill me-2"></i> Clientes Registrados</h2>
            <button class="bg-[#0578BE] hover:bg-[#16509D] text-white text-sm px-4 py-1.5 rounded-md transition" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                <i class="bi bi-person-plus-fill me-1"></i> Crear Cliente
            </button>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="bg-[#7CB9E6] text-[#16509D] px-4 py-2 rounded mb-4 shadow-sm">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if($clientes->isEmpty())
                <div class="bg-[#EAF4FB] border-l-4 border-[#0578BE] text-[#16509D] p-4 rounded shadow-sm">
                    <i class="bi bi-info-circle me-2"></i>No hay clientes registrados aún.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-800 border rounded">
                        <thead class="bg-[#7CB9E6] text-[#16509D] border-b border-[#9EA1A2]">
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Nombre</th>
                                <th class="px-4 py-2 text-left">NIT</th>
                                <th class="px-4 py-2 text-left">Teléfono</th>
                                <th class="px-4 py-2 text-left">Registrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr class="border-b hover:bg-[#F2F7FB]">
                                    <td class="px-4 py-2">{{ $cliente->id }}</td>
                                    <td class="px-4 py-2">{{ $cliente->nombre }}</td>
                                    <td class="px-4 py-2">{{ $cliente->nit ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $cliente->telefono ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $cliente->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Crear nuevo cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formNuevoCliente" class="modal-content" method="POST" action="{{ route('clientes.store') }}">
      @csrf
      <div class="modal-header bg-[#16509D] text-white">
        <h5 class="modal-title" id="modalNuevoClienteLabel">Nuevo Cliente</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="nombre" class="form-label text-[#16509D]">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control border-[#9EA1A2]" required>
        </div>
        <div class="mb-3">
            <label for="nit" class="form-label text-[#16509D]">NIT</label>
            <input type="text" name="nit" id="nit" class="form-control border-[#9EA1A2]">
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label text-[#16509D]">Teléfono</label>
            <input type="text" name="telefono" id="telefono" class="form-control border-[#9EA1A2]">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn text-white" style="background-color: #0578BE;">Guardar Cliente</button>
      </div>
    </form>
  </div>
</div>
@endsection
