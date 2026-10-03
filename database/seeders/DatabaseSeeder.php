<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Lillyan Cardoso Zalamena',
            'email' => 'lillycardoso02@gmail.com',
            'role' => UserRole::Ti,
        ]);

        User::factory()->create([
            'name' => 'Usuário Demo',
            'email' => 'usuario@flowkly.test',
            'role' => UserRole::User,
        ]);
    }
}
