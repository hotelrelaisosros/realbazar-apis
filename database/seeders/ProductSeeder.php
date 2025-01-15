<?php

namespace Database\Seeders;

use App\Enums\GemStoneFacting;
use Illuminate\Support\Facades\DB;

use App\Models\AccentStoneTypes;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\BandWidth;
use App\Models\BespokeCustomization;
use App\Models\BespokeCustomizationType;
use App\Models\BirthStone;
use App\Models\Gemshape;
use App\Models\MetalTypeCategory;
use App\Models\ProductEnum;
use App\Models\ProductVariation;
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

        $metal_type_category = [
            ['title' => 'Gold', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Golg Platinum', "image" => "upload/metal_types/bspoke/image1.png", "type" => "WG"],
            ['title' => 'Gold', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Golg Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
        ];
        foreach ($metal_type_category as $stoneType) {
            MetalTypeCategory::create($stoneType);
        }

        $gem_shapes = [
            ['name' => 'Gold', "image" => "upload/images/gemshapes/image1.png"],
            ['name' => 'Gold', "image" => "upload/images/gemshapes/image1.png"],
            ['name' => 'Gold', "image" => "upload/images/gemshapes/image1.png"],
            ['name' => 'Gold', "image" => "upload/images/gemshapes/image1.png"],
            ['name' => 'Gold', "image" => "upload/images/gemshapes/image1.png"],
        ];
        foreach ($gem_shapes as $stoneType) {
            Gemshape::create($stoneType);
        }




        $bespoke_customization_style = [
            ['name' => 'Pavé Bridge', "image" => "upload/images/bspoke/image1.png"],
            ['name' => 'Pavé Band', "image" => "upload/images/bspoke/image1.png"],
            ['name' => 'Hidden Halo', "image" => "upload/images/bspoke/image1.png"],
        ];
        $data = [
            [
                ['name' => 'Moissanite', 'price' => '500'],

                ['name' => 'Lab Diamond', 'price' => '500'],
            ],
            [
                ['name' => 'Moissanite', 'price' =>  '500'],
                ['name' => 'Lab Diamond', 'price' => '500'],
            ],
            [
                ['name' => 'Moissanite', 'price' => '500'],
                ['name' => 'Lab Diamond', 'price' =>  '500'],
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


        //variation seeder

        $metal_type_category = [
            ['title' => 'Gold', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Golg Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Gold', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
            ['title' => 'Golg Platinum', "image" => "upload/images/metal_types/image1.png", "type" => "WG"],
        ];
        foreach ($metal_type_category as $stoneType) {
            MetalTypeCategory::create($stoneType);
        }

        for ($i = 0; $i < 3; $i++) {
            $product = new Product();
            $product->user_id = 3;
            $product->sub_category_id = 1;
            $product->title = 'T-Shirt';
            $product->price = '5000';
            $product->discount_price = '1000';
            $product->tags = 'Standard';
            $product->desc = "V-Neck T-Shirt";
            $product->save();


            $imageCount = rand(1, 3);
            for ($j = 0; $j < $imageCount; $j++) {
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->image = 'image_' . $j . '_for_product_' . $product->id . '.jpg';
                $productImage->image_collection = json_encode(['image1.jpg', 'image2.jpg', 'image3.jpg']); // Example collection
                $productImage->small_image = 'small_image_' . $j . '_for_product_' . $product->id . '.jpg';
                $productImage->name = 'Product Image ' . ($j + 1);
                $productImage->save();
            }


            // Create ProductEnum for each product except one
            if ($i !== 0) {
                $productEnum = ProductEnum::create([
                    'metal_types' => json_encode([1, 2]),
                    'gem_shape_id' => 1,
                    'band_width_ids' => json_encode(BandWidth::pluck('id')->toArray()),
                    'accent_stone_type_ids' => json_encode(AccentStoneTypes::pluck('id')->toArray()),
                    'setting_height_ids' => json_encode(SettingHeight::pluck('id')->toArray()),
                    'prong_style_ids' => json_encode(ProngStyle::pluck('id')->toArray()),
                    'ring_size_ids' => json_encode(RingSize::pluck('id')->toArray()),
                    'bespoke_customization_ids' => json_encode(BespokeCustomization::pluck('id')->toArray()),
                    'birth_stone_ids' => json_encode(BirthStone::pluck('id')->toArray()),
                    'product_id' => $product->id
                ]);

                // Assign product_enum_id to the product
                // $product->product_enum_id = $productEnum->id;
                $product->save();
            } else {
                // Set product_enum_id to 0 for the first product
                // $product->product_enum_id = 0;
                $product->save();
            }
        }


        $metalTypes = MetalTypeCategory::pluck('id')->toArray();
        $gemShapes = Gemshape::pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();

        // Seed product variations
        foreach ($products as $productId) {
            ProductVariation::create([
                'product_id' => $productId,
                'title' => 'Variation for Product ' . $productId,
                'size' => rand(5, 12) . 'mm',
                'stock' => rand(10, 100),
                'price' => rand(100, 1000),
                'metal_type_id' => $metalTypes[array_rand($metalTypes)],
                'gem_shape_id' => $gemShapes[array_rand($gemShapes)],
            ]);
        }


        //updated data seeder
        $products = [
            [
                'sub_category_id' => 1,
                'title' => 'Product 1',
                'price' => 1000,
                'discount_price' => 900,
                'color' => 'Red',
                'desc' => 'This is the description of Product 1',
                'tags' => 'tag1,tag2',
                'status' => 'approved',
                'user_id' => 1,
                'configurations' => json_encode(['key1' => 'value1', 'key2' => 'value2']),
            ],
            [
                'sub_category_id' => 2,
                'title' => 'Product 2',
                'price' => 1500,
                'discount_price' => 1200,
                'color' => 'Blue',
                'desc' => 'This is the description of Product 2',
                'tags' => 'tag3,tag4',
                'status' => 'approved',
                'user_id' => 1,
                'configurations' => json_encode(['key3' => 'value3', 'key4' => 'value4']),
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Create 3 variations for each product
            for ($i = 1; $i <= 3; $i++) {
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'title' => "{$product->title} Variation $i",
                    'size' => 'Size ' . $i,
                    'stock' => rand(10, 50),
                    'price' => $product->price + $i * 100,
                    'metal_type_id' => 1,
                    'gem_shape_id' => 1,
                ]);

                // Create 1 image for each variation
                ProductImage::create([
                    'product_id' => $product->id,
                    'variant_id' => $variation->id,
                    'name' => "{$variation->title} Image",
                    'image' => "variation_{$variation->id}_image.jpg",
                    'image_collection' => json_encode(["variation_{$variation->id}_image.jpg"]),
                    'small_image' => "variation_{$variation->id}_small.jpg",
                    'type' => 'main',
                ]);
            }
        }



        DB::table('gem_stones')->insert([
            [
                'type' => 'M',
                'carat' => 1.2,
                'shape' => 'Round',
                'dimension' => '5x5x3 mm',
                'faceting' => GemStoneFacting::B->value,
                'price' => 2500.50,
                'gemstone_color_id' => 1,
                'color' => 'Blue',
                'clarity' => 'VVS1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'LGD',
                'carat' => 2.5,
                'shape' => 'Oval',
                'dimension' => '7x5x3.5 mm',
                'faceting' => GemStoneFacting::B->value,
                'price' => 5000.75,
                'gemstone_color_id' => 2,
                'color' => 'Pink',
                'clarity' => 'VS2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'M',
                'carat' => 3.0,
                'shape' => 'Square',
                'dimension' => '6x6x4 mm',
                'faceting' => GemStoneFacting::B->value,
                'price' => 7500.00,
                'gemstone_color_id' => 3,
                'color' => 'Green',
                'clarity' => 'SI1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'LGD',
                'carat' => 1.8,
                'shape' => 'Elongated Cushion',
                'dimension' => '8x4x2.5 mm',
                'faceting' => GemStoneFacting::B->value,
                'price' => 3000.25,
                'gemstone_color_id' => 4,
                'color' => 'Yellow',
                'clarity' => 'IF',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('birth_stones')->insert([
            [
                'name' => 'Garnet',
                'price' => 120.50,
                'description' => 'A deep red gemstone associated with January.',
                'image' => 'garnet.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Amethyst',
                'price' => 90.75,
                'description' => 'A purple quartz associated with February.',
                'image' => 'amethyst.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Aquamarine',
                'price' => 150.00,
                'description' => 'A pale blue gemstone associated with March.',
                'image' => 'aquamarine.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Diamond',
                'price' => 500.00,
                'description' => 'A clear gemstone associated with April.',
                'image' => 'diamond.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
