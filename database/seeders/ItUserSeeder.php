<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Project Leader
            ['name' => 'Rastansyah',    'username' => 'rastansyah',    'role' => 'project_leader', 'tim' => 'internal'],
            ['name' => 'Jefry',         'username' => 'jefry',         'role' => 'project_leader', 'tim' => 'eksternal'],

            // BA Internal
            ['name' => 'BA Internal 1', 'username' => 'ba_internal1',  'role' => 'business_analyst', 'tim' => 'internal'],
            ['name' => 'BA Internal 2', 'username' => 'ba_internal2',  'role' => 'business_analyst', 'tim' => 'internal'],
            ['name' => 'BA Internal 3', 'username' => 'ba_internal3',  'role' => 'business_analyst', 'tim' => 'internal'],

            // BA Eksternal
            ['name' => 'BA Eksternal 1','username' => 'ba_eksternal1', 'role' => 'business_analyst', 'tim' => 'eksternal'],
            ['name' => 'BA Eksternal 2','username' => 'ba_eksternal2', 'role' => 'business_analyst', 'tim' => 'eksternal'],
            ['name' => 'BA Eksternal 3','username' => 'ba_eksternal3', 'role' => 'business_analyst', 'tim' => 'eksternal'],

            // Developer Internal
            ['name' => 'Dev Internal 1','username' => 'dev_internal1', 'role' => 'developer', 'tim' => 'internal'],
            ['name' => 'Dev Internal 2','username' => 'dev_internal2', 'role' => 'developer', 'tim' => 'internal'],
            ['name' => 'Dev Internal 3','username' => 'dev_internal3', 'role' => 'developer', 'tim' => 'internal'],
            ['name' => 'Dev Internal 4','username' => 'dev_internal4', 'role' => 'developer', 'tim' => 'internal'],

            // Developer Eksternal
            ['name' => 'Dev Eksternal 1','username' => 'dev_eksternal1','role' => 'developer', 'tim' => 'eksternal'],
            ['name' => 'Dev Eksternal 2','username' => 'dev_eksternal2','role' => 'developer', 'tim' => 'eksternal'],
            ['name' => 'Dev Eksternal 3','username' => 'dev_eksternal3','role' => 'developer', 'tim' => 'eksternal'],
            ['name' => 'Dev Eksternal 4','username' => 'dev_eksternal4','role' => 'developer', 'tim' => 'eksternal'],
        ];

        foreach ($users as $user) {
            User::create([
                'name'     => $user['name'],
                'email'    => $user['username'] . '@example.com',
                'username' => $user['username'],
                'dept'     => 'IT',
                'role'     => $user['role'],
                'tim'      => $user['tim'],
                'password' => bcrypt('wahana123'),
            ]);
        }
    }
}
