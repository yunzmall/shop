<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-08-25
 * Time: 10:57
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


use app\common\facades\Setting;

class CollectHostService
{
    protected $host;

    public function __construct($host)
    {
        $this->host = $host;
    }

    public function handle()
    {
        $key_set = Setting::get('shop.key');

        $data = [
            'host' => $this->host,
            'key' => $key_set['key'],
            'secret' => $key_set['secret'],
        ];
        $hosts = json_encode($data);

        file_put_contents(base_path('static/yunshop/js/host.js'), $hosts);
    }
}