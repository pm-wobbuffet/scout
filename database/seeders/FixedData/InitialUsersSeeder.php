<?php

namespace Database\Seeders\FixedData;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'id' => 1,
        ], [
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@admin2.admin3',
            'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD')),
            'is_admin'  =>  true,
        ]);
    }
}
