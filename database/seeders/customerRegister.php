<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

class customerRegister extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([

            'name' => 'Ang Le Xuan',

            'email' => 'ang@gmail.com',

            'password' => bcrypt('Abcd!'),

            'mobile' => '012345678',

            'location' => 'none',

            'specialization' => 'none',

            'description' => 'none',

            'role' => 'customer',

            'doctorStatus' => 'none',

            'emailnotification' => false,

            'smsnotification' => false,
            
            'appnotification' => false,

        ]);
    }
}
