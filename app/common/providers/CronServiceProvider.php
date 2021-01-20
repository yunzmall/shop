<?php


namespace app\common\providers;


use app\console\Commands\CronRun;
use app\framework\Cron\Cron;
use Liebig\Cron\KeygenCommand;
use Liebig\Cron\Laravel5ServiceProvider;
use Liebig\Cron\ListCommand;

class CronServiceProvider extends Laravel5ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {

        $this->app->singleton('cron', function () {
            return new Cron();
        });

        $this->app->booting(function() {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Cron', 'Liebig\Cron\Facades\Cron');
        });

        $this->app->singleton('cron::command.run', function () {
            return new CronRun();
        });
        $this->commands('cron::command.run');

        $this->app->singleton('cron::command.list', function () {
            return new ListCommand;
        });
        $this->commands('cron::command.list');

        $this->app->singleton('cron::command.keygen', function () {
            return new KeygenCommand;
        });
        $this->commands('cron::command.keygen');
    }

}