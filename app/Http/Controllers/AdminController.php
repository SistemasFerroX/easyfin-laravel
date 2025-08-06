<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Podrías reutilizar las stats de Solicitudes o agregar nuevas
        return view('admin.dashboard');
    }

    public function users()
    {
        $users = User::paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $r, User $user)
    {
        $data = $r->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'role'  => 'required|in:user,admin,superadmin',
        ]);
        $user->update($data);
        return redirect()->route('admin.users')
                         ->with('success','Usuario actualizado');
    }
}
