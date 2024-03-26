<?php

namespace App\Console;

use Carbon\Carbon;
use App\Models\Option;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
      /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->scheduleRoadRunnerChecker($schedule);
        $this->scheduleInvoiceCommand($schedule);
    
    }

    /**
     * Schedule the RoadRunnerChecker command to run every 24 hours.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function scheduleRoadRunnerChecker(Schedule $schedule)
    {
        $schedule->command('check:roadrunner')->daily();
    }

    /**
     * Schedule the 'cron:invoice' command based on the configured factorisation interval.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function scheduleInvoiceCommand(Schedule $schedule)
    {
        $intervalDate = Option::where('name', 'factorisation_interval_date')->first();
        $cancelDate = Option::where('name', 'factorisation_cancel_date')->first();

        if ($intervalDate && $cancelDate) {
            $cancelDateTime = Carbon::parse($cancelDate->value);
            $hour = $cancelDateTime->hour + 1;
            $minute = $cancelDateTime->minute;

            switch ($intervalDate->value) {
                case 'daily':
                    $schedule->command('cron:invoice')->dailyAt("$hour:$minute");
                    break;
                case 'weekly':
                    $dayOfWeek = $cancelDateTime->dayOfWeek;
                    $dayOfWeekAdjusted = ($dayOfWeek == 0) ? 7 : $dayOfWeek;
                    $schedule->command('cron:invoice')->weeklyOn($dayOfWeekAdjusted, "$hour:$minute");
                    break;
                case 'monthly':
                    $schedule->command('cron:invoice')->monthlyOn($cancelDateTime->day, "$hour:$minute");
                    break;
                case 'yearly':
                    $schedule->command('cron:invoice')->yearlyOn($cancelDateTime->month, $cancelDateTime->day, "$hour:$minute");
                    break;
                default:
                    break;
            }
        }
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
