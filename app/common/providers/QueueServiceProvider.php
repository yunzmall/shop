<?php


namespace app\common\providers;


use app\framework\Queue\Worker;
use Illuminate\Contracts\Debug\ExceptionHandler;

class QueueServiceProvider extends \Illuminate\Queue\QueueServiceProvider
{

    /**
     * Register the queue worker.
     *
     * @return void
     */
    protected function registerWorker()
    {
        $this->app->singleton('queue.worker', function () {
			$isDownForMaintenance = function () {
				return $this->app->isDownForMaintenance();
			};
        	return new Worker(
        		$this->app['queue'],
                $this->app['events'],
                $this->app[ExceptionHandler::class],
                $isDownForMaintenance
            );
        });
    }
}