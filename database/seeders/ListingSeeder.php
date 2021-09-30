<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facilityKMId = DB::table('facilities')->insertGetId([
            'name' => 'Kamar Mandi Dalam',
            'icon_url' => 'https://static.mamikos.com/uploads/tags/b8yekhj4.png'
        ]);

        $facilityACId = DB::table('facilities')->insertGetId([
            'name' => 'AC',
            'icon_url' => 'https://static.mamikos.com/uploads/tags/b8yekhj4.png'
        ]);

        $facilityWifiId = DB::table('facilities')->insertGetId([
            'name' => 'Wifi',
            'icon_url' => 'https://static.mamikos.com/uploads/tags/HAAjIo8D.png'
        ]);

        $image1 = DB::table('photos')->insertGetId([
            'title' => 'Detail Kamar',
            'photo_url' => 'https://static.mamikos.com/uploads/cache/data/style/2021-09-10/15AeaSBL-540x720.jpg'
        ]);

        $roleOwner = Role::where('name', 'owner')->first();

        $listing1 = new Listing();
        $listing1->title = 'Kost Apik Putri Mawar PJMI Tipe A Bintaro Tangerang Selatan 548237AI';
        $listing1->description = 'Kosan murah meriah untuk cewek';
        $listing1->address = 'Pondok Aren';
        $listing1->quantity = 4;
        $listing1->gender_allowed = 'female';
        $listing1->price = 1280000;
        $listing1->owner_id = $roleOwner->users()->first()->id;
        $listing1->save();
        $listing1->facilities()->attach([$facilityKMId, $facilityACId, $facilityWifiId]);
        $listing1->photos()->attach([$image1]);
    }
    
}
