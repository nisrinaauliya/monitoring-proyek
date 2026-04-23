<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VerifikatorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'CMD',
            'username' => 'cmd',
            'email'=> 'cmd@example.com',
            'dept' => 'CMD',
            'role' => 'user',
            'password' => bcrypt('wahana123'),
        ]);
        
        User::create([
            'name' => 'DINOV',
            'username' => 'dinov',
            'email'=> 'dinov@example.com',
            'dept' => 'DINOV',
            'role' => 'user',
            'password' => bcrypt('wahana123'),
        ]);
    }
}
