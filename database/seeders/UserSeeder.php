<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email'=> 'spektrumdelapan@gmail.com',
            'dept' => 'IT',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);
        
        User::create([
            'name' => 'Nisrina Auliya',
            'username' => 'nisrina',
            'email'=> 'nisrinaauliyamuth@gmail.com',
            'dept' => 'MKT',
            'role' => 'user',
            'password' => bcrypt('wahana123'),
        ]);

        User::create([
            'name' => 'Nayara Resya',
            'username' => 'nayara',
            'email'=> 'niseurina1001@gmail.com',
            'dept' => 'CMD',
            'role' => 'user',
            'password' => bcrypt('wahana123'),
        ]);

        User::create([
            'name' => 'Aliansyah',
            'username' => 'aliansyah',
            'email'=> 'woojinlatte211@gmail.com',
            'dept' => 'DINOV',
            'role' => 'user',
            'password' => bcrypt('wahana123'),
        ]);

        User::create([
            'name' => 'Elara',
            'username' => 'elara',
            'email'=> 'nisrinaauliyacareer@gmail.com',
            'dept' => 'IT',
            'role' => 'user',
            'password' => bcrypt('wahana123'),
        ]);
    }
}
