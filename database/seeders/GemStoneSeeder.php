<?php

namespace Database\Seeders;

use App\Models\GemStone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GemStoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
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
            GemStone::create($shape);
        }


        DB::table('gem_stones')->insert([
            [
                'type' => 'M',
                'carat' => 1.0,
                'shape' => 'Oval',
                'dimension' => '7 mm x 5 mm',
                'faceting' => 'Excellent',
                'price' => 1813.00,
                'gemstone_color_id' => 1,
                'color' => 'D',
                'clarity' => 'VVS1',
            ],
            [
                'type' => 'M',
                'carat' => 1.25,
                'shape' => 'Oval',
                'dimension' => '7.5x5.5mm',
                'faceting' => 'Excellent',
                'price' => 2569.00,
                'gemstone_color_id' => 1,
                'color' => 'D',
                'clarity' => 'VVS1',
            ],
            [
                'type' => 'M',
                'carat' => 1.5,
                'shape' => 'Oval',
                'dimension' => '8 mm x 6 mm',
                'faceting' => 'Excellent',
                'price' => 3022.00,
                'gemstone_color_id' => 1,  // Assume color ID for "D"
                'color' => 'D',
                'clarity' => 'VVS1',
            ],
            [
                'type' => 'LGD',
                'carat' => 1.01,
                'shape' => 'Oval',
                'dimension' => '7.95x5.70x3.49',
                'faceting' => 'Brilliant',
                'price' => 3299.00,
                'gemstone_color_id' => 2,  // Assume color ID for "E"
                'color' => 'E',
                'clarity' => 'VS2',
            ],
            [
                'type' => 'LGD',
                'carat' => 1.05,
                'shape' => 'Oval',
                'dimension' => '8.33x5.91x3.49',
                'faceting' => 'Brilliant',
                'price' => 3249.00,
                'gemstone_color_id' => 3,  // Assume color ID for "F"
                'color' => 'F',
                'clarity' => 'VVS2',
            ],
        ]);
    }
}
