<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
{
    \App\Models\User::create([
        'name'     => 'Admin',
        'email'    => 'admin@tudominio.com',
        'password' => bcrypt('tu_contraseña'),
    ]);
}

}
