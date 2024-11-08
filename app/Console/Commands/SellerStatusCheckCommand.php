<?php

namespace App\Console\Commands;

use App\Models\PackagePayment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SellerStatusCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seller:statusChange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WholeSaler or Retailer Status Change';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sellers = User::whereRelation('role', 'name', 'retailer')->orWhereRelation('role', 'name', 'wholesaler')->get();
        foreach ($sellers as  $seller) {
            $payment = PackagePayment::where('user_id', $seller->id)->where('end_date', '<', Carbon::now())->first();
            // $payment exist means expired payment;
            if (!empty($payment)) {
                $seller->is_active = 0;
                $seller->save();
            }
        }
    }
}
