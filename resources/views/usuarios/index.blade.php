@extends('layouts.app')

<style>
    .bg-orange-custom {
        background-color: #f7931e !important;
        color: #fff;
    }

    .badge-rol {
        background-color: #16509D;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .btn-navasan-main {
        background-color: #0578BE;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
    }

    .btn-navasan-main:hover {
        background-color: #16509D;
    }

    .btn-ver {
        background-color: #7CB9E6;
        color: white;
    }

    .btn-editar {
        background-color: #0578BE;
        color: white;
    }

    .btn-eliminar {
        background-color: #f7931e;
        color: white;
    }

    .btn-ver:hover {
        background-color: #5ca9dd;
        color: white;
    }

    .btn-editar:hover {
        background-color: #16509D;
    }

    .btn-eliminar:hover {
        background-color: #d47400;
    }

    .btn-sm i {
        vertical-align: middle;
        margin-right: 4px;
    }
</style>

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> Usuarios del sistema</h4>
            <a href="#" class="btn btn-navasan-main" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Usuario
            </a>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($usuarios->isEmpty())
            <div class="alert alert-info">No hay usuarios registrados aún.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead style="background-color: #7CB9E6;" class="text-dark">
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                        <tr class="text-center">
                            <td>{{ $usuario->id }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>
                                @forelse($usuario->getRoleNames() as $rol)
                                <span class="badge-rol">{{ $rol }}</span>
                                @empty
                                <span class="text-muted">Sin rol</span>
                                @endforelse
                            </td>
                            <td>
                                <button class="btn btn-sm btn-ver me-1" data-bs-toggle="modal" data-bs-target="#modalVerUsuario{{ $usuario->id }}">
                                    <i class="bi bi-eye-fill"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-editar me-1" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario{{ $usuario->id }}">
                                    <i class="bi bi-pencil-fill"></i> Editar
                                </button>
                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-eliminar" onclick="return confirm('¿Eliminar este usuario?')">
                                        <i class="bi bi-trash-fill"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@include('usuarios.partials.modal-crear')
@include('usuarios.partials.modal-editar')
@include('usuarios.partials.modal-ver')

@endsection