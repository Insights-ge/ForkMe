<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Insights Admin',
                'password' => Hash::make('0e9caf820'),
            ]
        );

        $this->command->call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'policies_and_permissions',
        ]);

        $this->command->call('shield:super-admin', [
            '--user' => 1,
            '--panel' => 'admin',
        ]);

    }
}
