<?php


namespace app\framework\Redis;


class RedisServiceProvider extends \Illuminate\Redis\RedisServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('redis', function ($app) {
			$redisConfig = $app->make('config')->get('database.redis', []);
            if (app()->runningInConsole()) {
                foreach ($redisConfig as &$item) {
                    if (isset($item['host'])) {
                        $item['read_write_timeout'] = -1;
                    }
                }
            }
            return new Database($app,$redisConfig['client'],$redisConfig);
        });
    }
}