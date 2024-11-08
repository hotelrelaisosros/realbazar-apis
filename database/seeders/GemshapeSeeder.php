<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gemshape;

class GemshapeSeeder extends Seeder
{

    public function run()
    {
        $gemShapes = [
            ['name' => 'Round', 'image' => 'https://via.placeholder.com/150x150.png?text=Round'],
            ['name' => 'Oval', 'image' => 'https://via.placeholder.com/150x150.png?text=Oval'],
            ['name' => 'Cushion', 'image' => 'https://via.placeholder.com/150x150.png?text=Cushion'],
            ['name' => 'Emerald', 'image' => 'https://via.placeholder.com/150x150.png?text=Emerald'],
            ['name' => 'Princess', 'image' => 'https://via.placeholder.com/150x150.png?text=Princess'],
            ['name' => 'Marquise', 'image' => 'https://via.placeholder.com/150x150.png?text=Marquise'],
        ];
        foreach ($gemShapes as $shape) {
            Gemshape::create($shape);
        }
    }
}
