<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Activity_owner;
use App\Models\Air_line;
use App\Models\Owner;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tourism_company;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vehicle_owner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
          'name' => 'simon',
          'email' => 'simon@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '3',
        ]);
        User::query()->create([
          'name' => 'feras',
          'email' => 'feras@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '4',
        ]);
        User::query()->create([
          'name' => 'bshara',
          'email' => 'bshara@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '4',
        ]);
        User::query()->create([
          'name' => 'michelle',
          'email' => 'michelle@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '4',
        ]);
        User::query()->create([
          'name' => 'moaaz',
          'email' => 'moaaz@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '4',
        ]);
        User::query()->create([
          'name' => 'simon2',
          'email' => 'simon2@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '4',
        ]);
        User::query()->create([
          'name' => 'simon3',
          'email' => 'simon3@gmail.com',
          'password' => '123123',
          'phone_number' => '0937523553',
          'status' => '0',
          'role_id' => '4',
        ]);

        Owner::query()->create([
          'user_id' => '3',
          'owner_category_id' => '1',
          'country_id' => '2',
          'location' => 'Alhamra, Lebanon',
          'description' => 'description for an accommodation in damascus',
        ]);
        Owner::query()->create([
          'user_id' => '4',
          'owner_category_id' => '1',
          'country_id' => '1',
          'location' => 'Damascus, Syria',
          'description' => 'description for a hotel in damascus',
        ]);
        Owner::query()->create([
          'user_id' => '5',
          'owner_category_id' => '2',
          'country_id' => '3',
          'location' => 'Daraa, Syria',
          'description' => 'description for an air line',
        ]);
        Owner::query()->create([
          'user_id' => '6',
          'owner_category_id' => '3',
          'country_id' => '4',
          'location' => 'Aleppo, Syria',
          'description' => 'description for a touresim company',
        ]);
        Owner::query()->create([
          'user_id' => '7',
          'owner_category_id' => '4',
          'country_id' => '5',
          'location' => 'Bab Toma, Damascus, Syria',
          'description' => 'description for a car owner in damascus',
        ]);
        Owner::query()->create([
          'user_id' => '8',
          'owner_category_id' => '5',
          'country_id' => '6',
          'location' => 'City Center, Damascus, Syria',
          'description' => 'description for an activity owner in damascus',
        ]);

        Accommodation::query()->create([
          'owner_id' => '1',
          'accommodation_type_id' => '3',
        ]);
        Accommodation::query()->create([
          'owner_id' => '2',
          'accommodation_type_id' => '1',
        ]);

        Air_line::query()->create([
          'owner_id' => '3',
          'air_line_name' => 'Damascus Wings',
        ]);

        Tourism_company::query()->create([
          'owner_id' => '4',
          'company_name' => 'Al asmar for tourism and traveling',
        ]);

        Vehicle_owner::query()->create([
          'owner_id' => '5',
          'owner_name' => 'simon dahdal',
        ]);

        Activity_owner::query()->create([
          'owner_id' => '6',
          'owner_name' => 'simon dhl',
          'activity_id' => '1'
        ]);

        Room::query()->create([
          'accommodation_id' => '2',
          'price' => '50',
          'offer_price' => '40',
          'area' => '35',
          'people_count' => '2',
          'description' => 'big room super delox for 2 people',
          'room_number' => '1'
        ]);
        Room::query()->create([
          'accommodation_id' => '2',
          'price' => '55',
          'offer_price' => '34',
          'area' => '28',
          'people_count' => '1',
          'description' => 'small room for single person',
          'room_number' => '2'
        ]);

        Vehicle::query()->create([
          'vehicle_owner_id' => '1',
          'car_type_id' => '1',
          'name' => 'BMW 2019',
          'people_count' => '5',
          'car_discription' => 'new car with much features related with high speed'
        ]);
        Vehicle::query()->create([
          'vehicle_owner_id' => '1',
          'car_type_id' => '2',
          'name' => 'BMW 2021',
          'people_count' => '7',
          'car_discription' => 'elecetrical car with different features'
        ]);

        Service::query()->create([
          'name' => 'free Wi_Fi'
        ]);
        Service::query()->create([
          'name' => 'open buffet'
        ]);
        Service::query()->create([
          'name' => 'free breakfast'
        ]);
        Service::query()->create([
          'name' => 'flexibility'
        ]);        

    }
}
