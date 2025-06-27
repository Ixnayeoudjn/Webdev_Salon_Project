<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $services = [
            ['name'=>'Massage','category'=>'Massage','duration'=>30,'price'=>350],
            ['name'=>'Haircut','category'=>'Haircut','duration'=>40,'price'=>500],
            ['name'=>'Permanent Wave','category'=>'Treatments','duration'=>120,'price'=>1300],
            ['name'=>'Root Perm','category'=>'Treatments','duration'=>60,'price'=>800],
            ['name'=>'Hair Relax','category'=>'Treatments','duration'=>120,'price'=>2000],
            ['name'=>'Hair Rebond','category'=>'Treatments','duration'=>120,'price'=>4000],
            ['name'=>'Hair Spa','category'=>'Treatments','duration'=>40,'price'=>700],
            ['name'=>'Full Permanent','category'=>'Color Vibrancy','duration'=>120,'price'=>1800],
            ['name'=>'Root Retouch','category'=>'Color Vibrancy','duration'=>120,'price'=>1400],
            ['name'=>'Highlights','category'=>'Color Vibrancy','duration'=>120,'price'=>1800],
            ['name'=>'Highlights w/ bleach','category'=>'Color Vibrancy','duration'=>120,'price'=>2300],
        ];
        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
