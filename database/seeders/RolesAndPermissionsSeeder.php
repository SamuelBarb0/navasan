<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // === Permisos generales ===
        $permisos = [
            'crear ordenes',
            'ver todas las ordenes',
            'ver historial',

            'crear productos',
            'crear clientes',

            'liberar insumos',
            'registrar insumos recibidos',

            'registrar impresion',
            'registrar acabados',
            'registrar revision',

            'ver costos',
            'ver reporte revisado',
            'editar inventario etiquetas',

            'ver facturacion',
            'crear facturacion',
            'descargar facturas',

            'ver devoluciones',
            'registrar devolucion',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // === Roles con sus permisos ===
        $roles = [
            'preprensa' => [
                'crear ordenes',
                'ver todas las ordenes',
                'crear productos',
                'crear clientes',
                'ver historial',
            ],
            'impresion' => [
                'registrar impresion',
            ],
            'acabados' => [
                'registrar acabados',
            ],
            'revision' => [
                'registrar revision',
                'ver reporte revisado',
            ],
            'logistica' => [
                'ver costos',
                'ver facturacion',
                'crear facturacion',
                'descargar facturas',
                'ver devoluciones',
                'registrar devolucion',
                'editar inventario etiquetas',
            ],
            'administrador' => Permission::pluck('name')->toArray(), // acceso total
        ];

        foreach ($roles as $rol => $permisos) {
            $role = Role::firstOrCreate(['name' => $rol]);
            $role->syncPermissions($permisos);
        }
    }
}
