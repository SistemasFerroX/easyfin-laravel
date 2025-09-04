<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct()
    {
        // Sólo acceden admin o super-admin
        $this->middleware(['auth', 'role:admin|super-admin']);
    }

    /** Dashboard Admin/SuperAdmin */
    public function index()
    {
        return view('admin.dashboard');
    }

    /** LISTA de usuarios (solo super-admin por rutas) */
    public function users(Request $request)
    {
        $q = User::query()->with('roles');

        if ($term = trim((string) $request->input('q'))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%");
            });
        }
        if ($role = $request->input('role')) {
            // Filtro por rol usando spatie/permission
            $q->role($role);
        }

        $users = $q->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /** FORM crear usuario (solo super-admin) */
    public function createUser()
    {
        // Si NO quieres que se puedan crear más super-admins, quita 'super-admin' de aquí
        $roles = ['user' => 'Usuario', 'admin' => 'Administrador', 'super-admin' => 'Superadmin'];
        return view('admin.users.create', compact('roles'));
    }

    /** GUARDAR usuario nuevo (solo super-admin) */
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'role'  => ['required', Rule::in(['user','admin','super-admin'])],
        ]);

        // Crea con contraseña temporal y envía enlace para que la cambie
        $tempPassword = Str::random(16);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($tempPassword),
        ]);
        $user->syncRoles([$data['role']]);

        // Enviar enlace de restablecimiento (invitación)
        try {
            Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            // opcional: log warning
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado. Se envió un enlace para establecer contraseña.');
    }

    /** FORM editar usuario (solo super-admin) */
    public function editUser(User $user)
    {
        $roles = ['user' => 'Usuario', 'admin' => 'Administrador', 'super-admin' => 'Superadmin'];
        return view('admin.users.edit', compact('user','roles'));
    }

    /** ACTUALIZAR usuario (solo super-admin) */
    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['user','admin','super-admin'])],
            'password' => ['nullable','string','min:8'], // opcional: setear contraseña
        ]);

        // Proteger: no quitar el rol super-admin si es el ÚLTIMO superadmin
        if ($user->hasRole('super-admin') && $data['role'] !== 'super-admin') {
            if ($this->isLastSuperAdmin($user)) {
                return back()->with('error', 'No puedes quitar el último superadmin. Crea otro superadmin primero.');
            }
        }

        $user->fill([
            'name'  => $data['name'],
            'email' => $data['email'],
        ])->save();

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    /** ELIMINAR usuario (solo super-admin) */
    public function destroyUser(User $user)
    {
        // Proteger: no borrarse a sí mismo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Proteger: no borrar al ÚLTIMO superadmin
        if ($user->hasRole('super-admin') && $this->isLastSuperAdmin($user)) {
            return back()->with('error', 'No puedes eliminar al último superadmin.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }

    /** Helper: ¿es el último superadmin? */
    private function isLastSuperAdmin(User $candidate): bool
    {
        if (!$candidate->hasRole('super-admin')) return false;
        return User::role('super-admin')->count() <= 1;
    }
}
