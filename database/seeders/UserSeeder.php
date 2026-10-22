<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Main Admin',
            'email'    => 'admin@elysium.com',
            'password' => Hash::make('123456'), // change later
            'role'     => 'admin',               // your role field
            'status'   => 'active',              // or 1 if boolean
            'nid'      => '123456789',
            'phone'    => '0780000000',
        ]);
    }
}
