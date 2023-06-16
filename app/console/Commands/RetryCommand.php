<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/11/11
 * Time: 16:27
 */

namespace app\console\Commands;


class RetryCommand extends \Illuminate\Queue\Console\RetryCommand
{
	/**
	 * Retry the queue job.
	 *
	 * @param  \stdClass  $job
	 * @return void
	 */
	protected function retryJob($job)
	{
		$this->laravel['queue']->connection($job['connection'])->pushRaw(
			$this->resetAttempts($job['payload']), $job['queue']
		);
	}
}