<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Department;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'refund@test.com'],
            [
                'name' => 'Refund Officer',
                'password' => Hash::make('password'),
                'department' => Department::REFUND,
                'role' => UserRole::REFUND_OFFICER,
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'commercial@test.com'],
            [
                'name' => 'Commercial',
                'password' => Hash::make('password'),
                'department' => Department::COMMERCIAL,
                'role' => UserRole::COMMERCIAL,
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'audit@test.com'],
            [
                'name' => 'Audit',
                'password' => Hash::make('password'),
                'department' => Department::AUDIT,
                'role' => UserRole::AUDIT,
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'finance@test.com'],
            [
                'name' => 'Finance',
                'password' => Hash::make('password'),
                'department' => Department::FINANCE,
                'role' => UserRole::FINANCE,
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'treasury@test.com'],
            [
                'name' => 'Treasury',
                'password' => Hash::make('password'),
                'department' => Department::TREASURY,
                'role' => UserRole::TREASURY,
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'department' => Department::REFUND,
                'role' => UserRole::SUPER_ADMIN,
                'active' => true,
            ]
        );
    }
}