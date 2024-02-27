<?php

namespace App\Console;

use App\Jobs\OvertimeList_Daily;
use App\Jobs\OvertimeList_YearToStartOfWeek;
use App\Jobs\UpdateEmployeeTimeOff;
use App\Jobs\UpdateSharepointWCFCustomerStats;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //disabling all jobs for now


        //schedule OvertimeList_YearToStartOfWeek weekly on Monday at 001
        //$schedule->job(new OvertimeList_YearToStartOfWeek)->weeklyOn(1,'0:01');

        //schedule OvertimeList_Daily to run daily before midnight
        //$schedule->job(new OvertimeList_Daily)->dailyAt('23:59');

        //schedule UpdateEmployeeTimeOff to every 15 minutes
        $schedule->job(new UpdateEmployeeTimeOff)->everyFifteenMinutes();

        //schedule UpdateSharepointWCFCustomerStats twice daily at 700 and 1300
        $schedule->job(new UpdateSharepointWCFCustomerStats)->twiceDaily(7, 13);

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
