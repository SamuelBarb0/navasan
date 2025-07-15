@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow rounded-4">
        <div class="card-header text-white rounded-top-4 d-flex justify-content-between align-items-center py-3 px-4"
             style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-tags-fill me-2"></i> Categorías</h4>
            <button class="btn btn-sm text-white" style="background-color: #0578BE;" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                <i class="bi bi-plus-circle me-1"></i> Nueva Categoría
            </button>
        </div>

        <div class="card-body px-4 py-4" style="background-color: #F9FAFB;">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($categorias->isEmpty())
                <div class="alert" style="background-color: #7CB9E6; color: #16509D;">
                    No hay categorías registradas actualmente.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle shadow-sm">
                        <thead style="background-color: #7CB9E6;" class="text-dark text-center">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categorias as $categoria)
                                <tr>
                                    <td>{{ $categoria->nombre }}</td>
                                    <td>{{ $categoria->descripcion ?? '-' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarCategoria{{ $categoria->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal editar categoría --}}
                                @include('partials.editar-categoria', ['categoria' => $categoria])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal crear categoría --}}
@include('partials.crear-categoria')

@endsection
