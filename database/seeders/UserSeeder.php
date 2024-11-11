<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new User();
        $admin->role_id = 1;
        $admin->username = 'admin';
        // $admin->first_name = 'admin';
        $admin->email = 'admin@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();

        $admin = new User();
        $admin->role_id = 2;
        $admin->username = 'superadmin';
        // $admin->first_name = 'superadmin';
        $admin->email = 'superadmin@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();

        $admin = new User();
        $admin->role_id = 3;
        $admin->username = 'user';
        $admin->first_name = 'user';
        $admin->email = 'user@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();

        $admin = new User();
        $admin->role_id = 4;
        $admin->username = 'wholesaler';
        $admin->first_name = 'wholesaler';
        $admin->email = 'wholesaler@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();

        $admin = new User();
        $admin->role_id = 5;
        $admin->username = 'retailer';
        $admin->first_name = 'retailer';
        $admin->email = 'retailer@gmail.com';
        $admin->password = Hash::make(12345678);
        $admin->is_active = true;
        $admin->save();
    }
}
