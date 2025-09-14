<?php

namespace Database\Seeders\FixedData;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
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
            'name' => env('INITIAL_ADMIN_USERNAME', 'admin'),
            'email' => 'admin@admin.site',
            'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD')),
            'is_admin'  =>  true,
        ]);
    }
}
