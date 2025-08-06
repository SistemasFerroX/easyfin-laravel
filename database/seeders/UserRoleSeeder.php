<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // Busca al super admin por su email y asígnale el rol
        $super = User::firstWhere('email', 'superadmin@tuapp.com');
        if ($super) {
            $super->assignRole('Super Admin');
        }

        // Busca al admin y asígnale el rol
        $admin = User::firstWhere('email', 'admin@tuapp.com');
        if ($admin) {
            $admin->assignRole('Admin');
        }

        // Busca al usuario genérico y asígnale el rol
        $user = User::firstWhere('email', 'user@tuapp.com');
        if ($user) {
            $user->assignRole('User');
        }
    }
}
