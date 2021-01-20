<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-01-06
 * Time: 15:51
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

namespace app\common\services;


use Ixudra\Curl\Facades\Curl;

class ShopCollectService
{
    public $url;

    public function __construct()
    {
        $this->url = 'https://s.yunzmall.com';
        //$this->url = 'http://www.wfmarket.com'; //测试
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