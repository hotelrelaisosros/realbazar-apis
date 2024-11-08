<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\UnpaidRegisterUser;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PaymentFailedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymentFailed:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Failed Payment Data delete after 48 hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = UnpaidRegisterUser::where('created_at', '<=', Carbon::now()->subDay(2))->get();
        foreach ($users as  $user) {
            $user->delete();
        }

        $orders = Order::where('pay_status','unpaid')->where('created_at', '<=', Carbon::now()->subDay(2))->get();
        foreach ($orders as  $order) {
            $order->delete();
        }
    }
}
