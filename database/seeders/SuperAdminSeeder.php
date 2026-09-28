<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@coba.test',
                'password' => Hash::make('super123'),
                'role' => 'superadmin',
            ]
        );  
    }
}
