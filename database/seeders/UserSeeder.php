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
        $accounts = [
            [
                'email' => 'owner@smartcoop.com',
                'name' => 'Owner SmartCoop',
                'role' => 'owner',
                'base_salary' => 5000000,
            ],
            [
                'email' => 'harries@smartcoop.com',
                'name' => 'Muhammad Harries Rasyadi',
                'role' => 'worker',
                'base_salary' => 2800000,
            ],
            [
                'email' => 'danang@smartcoop.com',
                'name' => 'Danang Onky Romadhoni',
                'role' => 'worker',
                'base_salary' => 3000000,
            ],
            [
                'email' => 'mujawiri@smartcoop.com',
                'name' => 'Mujawiri Apandie',
                'role' => 'worker',
                'base_salary' => 2700000,
            ],
        ];

        $verifiedAt = now();

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'role' => $account['role'],
                    'base_salary' => $account['base_salary'],
                ]
            );

            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => $verifiedAt])->save();
            }
        }
    }
}
