<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Owner Account
        User::updateOrCreate(
            ['email' => 'owner@smartcoop.com'],
            [
                'name' => 'Owner SmartCoop',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'base_salary' => 5000000,
            ]
        );

        // 3 Worker Accounts
        User::updateOrCreate(
            ['email' => 'harries@smartcoop.com'],
            [
                'name' => 'Muhammad Harries Rasyadi',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'base_salary' => 2800000,
            ]
        );

        User::updateOrCreate(
            ['email' => 'danang@smartcoop.com'],
            [
                'name' => 'Danang Onky Romadhoni',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'base_salary' => 3000000,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mujawiri@smartcoop.com'],
            [
                'name' => 'Mujawiri Apandie',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'base_salary' => 2700000,
            ]
        );
    }
}
