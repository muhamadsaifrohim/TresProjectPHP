<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lostnfound.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin12345'), // ganti nanti setelah tes
                'role' => 'admin',
            ]
        );
    }
}