<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function edit(Request $request)
    {
        $user  = $request->user();
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();

        return view('profile.edit', compact('user', 'roles'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Reglas básicas
        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];

        // Si quiere cambiar contraseña
        if ($request->filled('password') || $request->filled('current_password') || $request->filled('password_confirmation')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password']         = ['required', 'confirmed', Password::defaults()];
        }

        $data = $request->validate($rules);

        $previousEmail = $user->email;

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (array_key_exists('password', $data)) {
            $user->password = Hash::make($data['password']);
        }

        // Si cambia el email y el usuario verifica correo, invalida verificación y reenvía notificación
        $emailChanged = $previousEmail !== $data['email'];
        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();

        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
