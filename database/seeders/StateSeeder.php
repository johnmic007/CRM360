<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['name' => 'Andhra Pradesh', 'code' => 'AP'],
            ['name' => 'Arunachal Pradesh', 'code' => 'AR'],
            ['name' => 'Assam', 'code' => 'AS'],
            ['name' => 'Bihar', 'code' => 'BR'],
            ['name' => 'Chhattisgarh', 'code' => 'CG'],
            ['name' => 'Goa', 'code' => 'GA'],
            ['name' => 'Gujarat', 'code' => 'GJ'],
            ['name' => 'Haryana', 'code' => 'HR'],
            ['name' => 'Himachal Pradesh', 'code' => 'HP'],
            ['name' => 'Jharkhand', 'code' => 'JH'],
            ['name' => 'Karnataka', 'code' => 'KA'],
            ['name' => 'Kerala', 'code' => 'KL'],
            ['name' => 'Madhya Pradesh', 'code' => 'MP'],
            ['name' => 'Maharashtra', 'code' => 'MH'],
            ['name' => 'Manipur', 'code' => 'MN'],
            ['name' => 'Meghalaya', 'code' => 'ML'],
            ['name' => 'Mizoram', 'code' => 'MZ'],
            ['name' => 'Nagaland', 'code' => 'NL'],
            ['name' => 'Odisha', 'code' => 'OR'],
            ['name' => 'Punjab', 'code' => 'PB'],
            ['name' => 'Rajasthan', 'code' => 'RJ'],
            ['name' => 'Sikkim', 'code' => 'SK'],
            ['name' => 'Tamil Nadu', 'code' => 'TN'],
            ['name' => 'Telangana', 'code' => 'TG'],
            ['name' => 'Tripura', 'code' => 'TR'],
            ['name' => 'Uttar Pradesh', 'code' => 'UP'],
            ['name' => 'Uttarakhand', 'code' => 'UK'],
            ['name' => 'West Bengal', 'code' => 'WB'],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }
}
