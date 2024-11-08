<?php

namespace App\Console\Commands;

use App\Models\CompleteDemandProduct;
use App\Models\DemandProduct;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DemandProductDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demand:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demand Products delete after 24 hour';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $demadProducts = DemandProduct::where('timer', '<=', Carbon::now()->subDay())->get();
        foreach ($demadProducts as  $demadProduct) {
            $demadProduct->delete();
        }
        // $completeDemadProducts = CompleteDemandProduct::where('created_at', '<=', Carbon::now()->subDay())->get();
        // foreach ($completeDemadProducts as  $completeDemadProduct) {
        //     $completeDemadProduct->delete();
        // }
    }
}
