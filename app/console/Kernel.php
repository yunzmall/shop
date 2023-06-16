<?php

namespace app\console;
defined('IN_IA') or define('IN_IA',true);

use app\console\Commands\FixMemberRelease;
use app\console\Commands\MemberRelease;
use app\console\Commands\VendorPublish;
use app\framework\Redis\RedisServiceProvider;
use app\console\Commands\WriteFrame;
use app\framework\Foundation\Bootstrap\SetRequestForConsole;
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
        'app\console\Commands\UpdateVersion',
        'app\console\Commands\RepairWithdraw',
        'app\console\Commands\Test',
        'app\console\Commands\Shop',
        'app\console\Commands\WechatOpen',
        'app\console\Commands\RebuildDb',
        'app\console\Commands\MigrateHFLevelExcelData',
        'app\console\Commands\MigrateMemberDistributor',
        'app\console\Commands\UpdateInviteCode',
        'app\console\Commands\CorrectionSupplierData',
        'app\console\Commands\RetryCommand',
        WriteFrame::class,
        MemberRelease::class,
        FixMemberRelease::class,
        VendorPublish::class,
    ];
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \app\framework\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
//        $schedule->command('ZhuzherCurl')->everyMinute();
//        $schedule->command('CreditSeed')->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
