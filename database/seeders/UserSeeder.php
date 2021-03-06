<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ownerRoleId = DB::table('roles')->insertGetId([
            'name' => 'owner',
        ]);

        $userRoleId = DB::table('roles')->insertGetId([
            'name' => 'user',
        ]);

        $userOwner = new User();
        $userOwner->name = 'Kos Owner';
        $userOwner->email = 'owner@email.com';
        $userOwner->password = bcrypt('owner');
        $userOwner->save();
        $userOwner->roles()->attach($ownerRoleId);

        $userOwner = new User();
        $userOwner->name = 'Kos Owner Dua';
        $userOwner->email = 'owner2@email.com';
        $userOwner->password = bcrypt('owner');
        $userOwner->save();
        $userOwner->roles()->attach($ownerRoleId);

        $userReguler = new User();
        $userReguler->name = 'User Reguler';
        $userReguler->email = 'reg.user@email.com';
        $userReguler->password = bcrypt('reguler');
        $userReguler->credit = 20;
        $userReguler->save();
        $userReguler->roles()->attach($userRoleId);

        $userPremium = new User();
        $userPremium->name = 'User Premium';
        $userPremium->email = 'premium.user@email.com';
        $userPremium->password = bcrypt('premium');
        $userPremium->is_premium_user = 1;
        $userPremium->credit = 40;
        $userPremium->save();
        $userPremium->roles()->attach($userRoleId);
    }
    
}
