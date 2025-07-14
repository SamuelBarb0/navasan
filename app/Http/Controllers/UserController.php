<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->get(); // trae los usuarios con sus roles
        $roles = Role::all(); // importante: esto es lo que faltaba
        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole($data['roles']); // asigna múltiples

        return redirect()->back()->with('success', 'Usuario creado correctamente');
    }


    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $usuario->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $usuario->syncRoles($data['roles']); // sincroniza múltiples

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }


    public function destroy(User $usuario)
    {
        try {
            // Elimina todos los roles asignados (opcional, pero recomendable si hay relaciones en cascada)
            $usuario->syncRoles([]);

            // Luego elimina el usuario
            $usuario->delete();

            return redirect()->back()->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al eliminar el usuario.');
        }
    }
}
