<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->get();
        $roles    = Role::all();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            // 👇 sin confirmed
            'password' => ['required','string','min:8'],
            'roles'    => ['required','array'],
            'roles.*'  => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['roles']);

        return back()->with('success', 'Usuario creado correctamente');
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'email'   => ['required','email','max:255', Rule::unique('users','email')->ignore($usuario->id)],
            'roles'   => ['required','array'],
            'roles.*' => ['exists:roles,name'],
            // 👇 opcional y sin confirmed
            'password'=> ['nullable','string','min:8'],
        ]);

        $usuario->name  = $data['name'];
        $usuario->email = $data['email'];

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();
        $usuario->syncRoles($data['roles']);

        return back()->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        try {
            $usuario->syncRoles([]);
            $usuario->delete();
            return back()->with('success', 'Usuario eliminado correctamente');
        } catch (\Throwable $e) {
            return back()->with('error', 'Ocurrió un error al eliminar el usuario.');
        }
    }
}
