<?php


namespace app\framework\Database;


use app\common\facades\Setting;
use Illuminate\Support\Facades\Redis;
use function EasyWeChat\Payment\get_client_ip;

class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        if (app('db')->cacheSelect && app('db')->openCacheSelect()) {
            // 开启缓存
            $cacheKey = 'SELECT_CACHE:' . md5($query . json_encode($bindings));
            $result = Redis::get($cacheKey);
            if (!$result) {
                // 过期或未命中
                $result = parent::select($query, $bindings, $useReadPdo);
                Redis::setex($cacheKey, 120, json_encode($result));
            } else {
                $result = json_decode($result, true);
            }
        } else {
            $result = parent::select($query, $bindings, $useReadPdo);
        }
        return $result;
    }
}