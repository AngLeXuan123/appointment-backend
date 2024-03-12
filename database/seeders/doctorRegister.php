<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

class doctorRegister extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([

            'name' => 'Dillon Ang',

            'email' => 'dillonang02@gmail.com',

            'password' => bcrypt('Abcd!'),

            'mobile' => '012345678',

            'location' => 'New York',

            'specialization' => 'Cardiology',

            'description' => 'I love bones',

            'role' => 'doctor',

            'doctorStatus' => 'Accepted',

            'emailnotification' => false,

            'smsnotification' => false,
            
            'appnotification' => false,

        ]);
    }
}
