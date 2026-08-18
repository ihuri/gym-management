<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para criar usuário administrador padrão de desenvolvimento.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'dev@test.com'],
            [
                'name' => 'Desenvolvedor Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
