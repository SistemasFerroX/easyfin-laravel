<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        // Asegura que solo usuarios con rol Admin o Super Admin accedan
        $this->middleware(['auth', 'role:Admin|Super Admin']);
    }

    public function index()
    {
        // Panel de control para Admin/Super Admin
        return view('admin.dashboard');
    }

    public function users()
    {
        // Listado de usuarios paginado
        $users = User::paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function editUser(User $user)
    {
        // Formulario de edición de usuario
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        // Validación de datos
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'  => [
                'required',
                // Asegúrate de usar los nombres exactos de los roles creados
                'in:Super Admin,Admin,User',
            ],
        ]);

        // Actualizar nombre y email
        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        // Sincronizar roles (elimina roles previos y asigna el nuevo)
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('admin.users')
            ->with('success', 'Usuario actualizado correctamente.');
    }
}