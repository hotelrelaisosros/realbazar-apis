<?php

namespace Database\Seeders;

use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $package = new Package();
        $package->create([
            'name' => 'Free',
            'date' => Carbon::now(),
            'time' => '1',
            'period' => 'month',
            'amount' => '0',
            'product_qty' => '25',
            'is_active' => false,
        ]);
    }
}
