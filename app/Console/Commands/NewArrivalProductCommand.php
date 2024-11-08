<?php

namespace App\Console\Commands;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NewArrivalProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productnewArrival:statusChange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'After 2 days New Arrival Products Status Change';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $products = Product::where('is_new_arrival', true)->get();
        foreach ($products as  $product) {
            $date = Carbon::now();
            $days = $product->created_at->addDays(2);
            if ($date > $days) {
                $product->is_new_arrival = 0;
                $product->save();
            }
        }
    }
}
