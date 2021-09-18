<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-08-25
 * Time: 11:06
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\common\listeners;


use Illuminate\Foundation\Bus\DispatchesJobs;

class CollectHostListener
{
    use DispatchesJobs;

    protected $url;

    public function __construct()
    {
        $this->url = 'https://yun.yunzmall.com';
    }

    public function handle()
    {
        $host_message = json_decode(file_get_contents(base_path('static/yunshop/js/host.js')), true);
        $host = $host_message['host']?:'';
        $key = $host_message['key']?:'';
        $secret = $host_message['secret']?:'';
        $data = [
            'host' => $host,
            'plugins' => $this->getPlugins(),
            'key' => $key,
            'secret' => $secret,
        ];
        $url = $this->url . '/api/plugin-collect/plugin-collect';
        $result = \Curl::to($url)
            ->withData($data)
            ->asJsonResponse(true)
            ->post();
        if ($result['result'] != 1) {
            \Log::debug('------授权系统请求获取插件信息接口失败------', $result);
        }
    }

    public function subscribe()
    {
        $second_rand = rand(0,59);
        $time_rand = rand(0,12);
        \Event::listen('cron.collectJobs', function () use ($second_rand, $time_rand) {
            \Cron::add('CollectHost', $second_rand . " " . $time_rand . ' * * *', function () {
                $this->handle();
                return;
            });
        });
    }

    public function getPlugins()
    {
        $plugin_name = [];
        $plugins = app('plugins')->getPlugins()->toArray();
        foreach ($plugins as $plugin) {
            $plugin_name[] = $plugin['name'];
        }
        return $plugin_name;
    }
}