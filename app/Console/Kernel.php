<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\NewArrivalProductCommand::class,
        Commands\SellerStatusCheckCommand::class,
        Commands\DemandProductDelete::class,
        Commands\PaymentFailedData::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('seller:statusChange')->everyMinute();
        $schedule->command('productnewArrival:statusChange')->everyMinute();
        $schedule->command('demand:delete')->everyMinute();
        $schedule->command('paymentFailed:delete')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
