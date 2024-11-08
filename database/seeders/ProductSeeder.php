<?php

namespace Database\Seeders;

use App\Models\AccentStoneTypes;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\BandWidth;
use App\Models\BespokeCustomization;
use App\Models\BespokeCustomizationType;
use App\Models\BirthStone;
use App\Models\ProductEnum;
use App\Models\ProngStyle;
use App\Models\RingSize;
use App\Models\SettingHeight;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = new Category();
        $subcategory = new SubCategory();

        $category->create(['name' => 'Jewellery', 'url' => 'menscollection', 'image' => 'category/category1.jpg']);
        $category->create(['name' => 'Womens Collection', 'url' => 'womenscollection', 'image' => 'category/category2.jpg']);
        $category->create(['name' => 'Kids Collection', 'url' => 'kidscollection', 'image' => 'category/category3.jpg']);
        $category->create(['name' => 'Health & Beauty', 'url' => 'health&beauty']);

        $subcategory->create(['category_id' => 1, 'name' => 'Rings', 'url' => 'jewellery/shoes', 'image' => 'subcategory/subcategory1.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'Shirts','url'=>'womenscollection/shirts','image'=>'subcategory/subcategory6.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'T-Shirts','url'=>'womenscollection/t-shirts','image'=>'subcategory/subcategory7.jpg']);
        // $subcategory->create(['category_id'=>1,'name'=>'Jeans','url'=>'womenscollection/jeans','image'=>'subcategory/subcategory8.jpg']);

        $subcategory->create(['category_id' => 2, 'name' => 'Clothes', 'url' => 'womenscollection/clothes', 'image' => 'subcategory/subcategory2.jpg']);
        $subcategory->create(['category_id' => 2, 'name' => 'Purse', 'url' => 'womenscollection/purse', 'image' => 'subcategory/subcategory3.jpg']);
        $subcategory->create(['category_id' => 2, 'name' => 'Jewellery', 'url' => 'womenscollection/jewellery', 'image' => 'subcategory/subcategory4.jpg']);
        $subcategory->create(['category_id' => 1, 'name' => 'Jeans', 'url' => 'menscollection/jeans', 'image' => 'subcategory/subcategory4.jpg']);

        $subcategory->create(['category_id' => 3, 'name' => 'Watch', 'url' => 'kidscollection/watch', 'image' => 'subcategory/subcategory9.jpg']);
        $subcategory->create(['category_id' => 3, 'name' => 'Shirts', 'url' => 'kidscollection/shirts', 'image' => 'subcategory/subcategory10.jpg']);
        $subcategory->create(['category_id' => 3, 'name' => 'T-Shirts', 'url' => 'kidscollection/t-shirts', 'image' => 'subcategory/subcategory11.jpg']);
        $subcategory->create(['category_id' => 3, 'name' => 'Jeans', 'url' => 'kidscollection/jeans', 'image' => 'subcategory/subcategory12.jpg']);

        $subcategory->create(['category_id' => 4, 'name' => 'Bath & Body', 'url' => 'health&beauty/bath&body']);
        $subcategory->create(['category_id' => 4, 'name' => 'Beauty Tools', 'url' => 'health&beauty/beautytools']);
        $subcategory->create(['category_id' => 4, 'name' => 'Hair Care', 'url' => 'health&beauty/haircare']);
        $subcategory->create(['category_id' => 4, 'name' => 'Makeup', 'url' => 'health&beauty/makeup']);


        $bandWidths = [
            ['name' => '1.6mm'],
            ['name' => '1.8mm'],
            ['name' => '2.0mm']
        ];

        foreach ($bandWidths as $width) {
            BandWidth::create($width);
        }
        $accentStoneTypes = [
            ['name' => 'diamond'],
            ['name' => 'missionate']
        ];

        foreach ($accentStoneTypes as $stoneType) {
            AccentStoneTypes::create($stoneType);
        }


        $setting_height = [
            ['name' => 'High setting'],
            ['name' => 'Low setting']
        ];
        foreach ($setting_height as $stoneType) {
            SettingHeight::create($stoneType);
        }

        $prong_style = [
            ['name' => 'Singular'],
            ['name' => 'Compass'],
            ["name" => "Double"]
        ];
        foreach ($prong_style as $stoneType) {
            ProngStyle::create($stoneType);
        }

        $bespoke_customization_style = [
            ['name' => 'Pavé Bridge', "image" => "upload/images/bspoke/image1.png"],
            ['name' => 'Pavé Band', "image" => "upload/images/bspoke/image1.png"],
            ['name' => 'Hidden Halo', "image" => "upload/images/bspoke/image1.png"],
        ];
        $data = [
            [
                ['name' => 'Moissanite', 'price' => 'ر.س507.00'],
                ['name' => 'Lab Diamond', 'price' => 'ر.س1,013.00']
            ],
            [
                ['name' => 'Moissanite', 'price' => 'ر.س633.00'],
                ['name' => 'Lab Diamond', 'price' => 'ر.س1,140.00']
            ],
            [
                ['name' => 'Moissanite', 'price' => 'ر.س507.00'],
                ['name' => 'Lab Diamond', 'price' => 'ر.س1,013.00']
            ]
        ];
        for ($i = 0; $i < count($bespoke_customization_style); $i++) {
            $bespoke_customization = BespokeCustomization::create([
                'name' => $bespoke_customization_style[$i]['name'],
                'image' => $bespoke_customization_style[$i]['image']
            ]);

            foreach ($data[$i] as $stone) {
                BespokeCustomizationType::create([
                    'name' => $stone['name'],
                    'price' => $stone['price'],
                    'bespoke_customization_id' => $bespoke_customization->id
                ]);
            }
        }





        $ringSizes = [
            'F',
            'F 1/2',
            'G',
            'G 1/2',
            'H',
            'H 1/2',
            'I',
            'I 1/2',
            'J',
            'J 1/2',
            'K',
            'K 1/2',
            'L',
            'L 1/2',
            'M',
            'M 1/2',
            'N',
            'N 1/2',
            'O',
            'O 1/2',
            'P',
            'P 1/2',
            'Q',
            'Q 1/2',
            'R',
            'R 1/2',
            'S',
            'S 1/2',
            'T',
            'T 1/2',
            'U',
            'U 1/2',
            'V',
            'V 1/2',
            'W',
            'W 1/2',
            'X',
            'X 1/2',
            'Y',
            'Y 1/2',
            'Z',
            'Z 1/2'
        ];

        foreach ($ringSizes as $size) {
            RingSize::create(['name' => $size]);
        }


        for ($i = 0; $i < 3; $i++) {
            $product = new Product();
            $product->user_id = 4;
            $product->sub_category_id = 1;
            $product->title = 'T-Shirt';
            $product->price = '5000';
            $product->discount_price = '1000';
            $product->tags = 'Standard';
            $product->desc = "V-Neck T-Shirt";
            $product->save();

            // Create ProductEnum for each product except one
            if ($i !== 0) {
                $productEnum = ProductEnum::create([
                    'metal_types' => json_encode(['1', '2']),
                    'gem_shape_id' => 1,
                    'default_metal_id' => 2,
                    'band_width_ids' => implode(',', BandWidth::pluck('id')->toArray()),
                    'accent_stone_type_ids' => implode(',', AccentStoneTypes::pluck('id')->toArray()),
                    'setting_height_ids' => implode(',', SettingHeight::pluck('id')->toArray()),
                    'prong_style_ids' => implode(',', ProngStyle::pluck('id')->toArray()),
                    'ring_size_ids' => implode(',', RingSize::pluck('id')->toArray()),
                    'bespoke_customization_ids' => implode(',', BespokeCustomization::pluck('id')->toArray()),
                    'birth_stone_ids' => implode(',', BirthStone::pluck('id')->toArray()),
                    'product_id' => $product->id
                ]);

                // Assign product_enum_id to the product
                $product->product_enum_id = $productEnum->id;
                $product->save();
            } else {
                // Set product_enum_id to 0 for the first product
                $product->product_enum_id = 0;
                $product->save();
            }
        }
    }
}
