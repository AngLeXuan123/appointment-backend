<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

class CreateAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([

            'name' => 'Ang',

            'email' => 'admin@gmail.com',

            'password' => bcrypt('Abcd!'),

            'mobile' => '012345678',

            'location' => 'none',

            'specialization' => 'none',

            'description' => 'none',

            'role' => 'admin',

            'doctorStatus' => 'none',

        ]);
    }
}
