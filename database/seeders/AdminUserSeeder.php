<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@reservassports.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'phone' => env('ADMIN_PHONE', '3000000000'),
                'role' => 'admin',
                'is_active' => true,
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin12345')),
            ]
        );
    }
}
