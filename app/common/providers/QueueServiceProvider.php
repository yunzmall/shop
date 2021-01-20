<?php


namespace app\common\providers;


use app\framework\Queue\Worker;

class QueueServiceProvider extends \Illuminate\Queue\QueueServiceProvider
{

    /**
     * Register the queue worker.
     *
     * @return void
     */
    protected function registerWorker()
    {
        $this->registerWorkCommand();

        $this->registerRestartCommand();

        $this->app->singleton('queue.worker', function ($app) {
            return new Worker(
                $app['queue'], $app['events'],
                $app['Illuminate\Contracts\Debug\ExceptionHandler']
            );
        });
    }
}