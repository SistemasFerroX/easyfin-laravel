<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Roles
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'User']);
        // Si luego necesitas permisos, también puedes crearlos:
        // Permission::firstOrCreate(['name' => 'edit articles']);
        // Role::findByName('Admin')->givePermissionTo('edit articles');
    }
}
