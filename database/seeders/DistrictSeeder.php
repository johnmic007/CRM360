<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            ['name' => 'Ariyalur', 'state_id' => 23],
            ['name' => 'Chengalpattu', 'state_id' => 23],
            ['name' => 'Chennai', 'state_id' => 23],
            ['name' => 'Coimbatore', 'state_id' => 23],
            ['name' => 'Cuddalore', 'state_id' => 23],
            ['name' => 'Dharmapuri', 'state_id' => 23],
            ['name' => 'Dindigul', 'state_id' => 23],
            ['name' => 'Erode', 'state_id' => 23],
            ['name' => 'Kallakurichi', 'state_id' => 23],
            ['name' => 'Kanchipuram', 'state_id' => 23],
            ['name' => 'Kanyakumari', 'state_id' => 23],
            ['name' => 'Karur', 'state_id' => 23],
            ['name' => 'Krishnagiri', 'state_id' => 23],
            ['name' => 'Madurai', 'state_id' => 23],
            ['name' => 'Mayiladuthurai', 'state_id' => 23],
            ['name' => 'Nagapattinam', 'state_id' => 23],
            ['name' => 'Namakkal', 'state_id' => 23],
            ['name' => 'Nilgiris', 'state_id' => 23],
            ['name' => 'Perambalur', 'state_id' => 23],
            ['name' => 'Pudukkottai', 'state_id' => 23],
            ['name' => 'Ramanathapuram', 'state_id' => 23],
            ['name' => 'Ranipet', 'state_id' => 23],
            ['name' => 'Salem', 'state_id' => 23],
            ['name' => 'Sivaganga', 'state_id' => 23],
            ['name' => 'Tenkasi', 'state_id' => 23],
            ['name' => 'Thanjavur', 'state_id' => 23],
            ['name' => 'Theni', 'state_id' => 23],
            ['name' => 'Thoothukudi', 'state_id' => 23],
            ['name' => 'Tiruchirappalli', 'state_id' => 23],
            ['name' => 'Tirunelveli', 'state_id' => 23],
            ['name' => 'Tirupathur', 'state_id' => 23],
            ['name' => 'Tiruppur', 'state_id' => 23],
            ['name' => 'Tiruvallur', 'state_id' => 23],
            ['name' => 'Tiruvannamalai', 'state_id' => 23],
            ['name' => 'Tiruvarur', 'state_id' => 23],
            ['name' => 'Vellore', 'state_id' => 23],
            ['name' => 'Viluppuram', 'state_id' => 23],
            ['name' => 'Virudhunagar', 'state_id' => 23],
        ];

        foreach ($districts as $district) {
            District::create($district);
        }
    }
}
