<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/5/9
 * Time: 15:51
 * Author: Merlin
 */

namespace app\process;


use app\common\helpers\YunSession;
use app\common\helpers\YunSessionMemcache;
use app\common\helpers\YunSessionRedis;
use app\common\services\Session;
use Illuminate\Support\Facades\Redis;
use Workerman\Connection\TcpConnection;

class WebSocketHandle
{

    protected $service;


    public function __construct(WebSocket $service)
    {
        $this->service = $service;
    }


    public function login(TcpConnection $connection, array $res)
    {
        $session_id = $res['session_id'];
        if (empty($session_id)) {
            return $connection->send(json_encode(['msg' => '授权失败', 'result' => 0]));
        }
        $session_data = Redis::hgetall('PHPSESSID:' . $session_id);
        $member_id = $this->getMemberId($session_data['data']);
        if (empty($member_id)) {
            return $connection->send(json_encode(['msg' => '授权失败', 'result' => 0]));
        }
        if (empty($res['group'])) {
            return $connection->send(json_encode(['msg' => '授权失败，没有分组', 'result' => 0]));
        }
        $connection->member_id = $member_id;
        $connection->is_login = 1;
        $this->service->setMember($member_id, $res['group'] , $connection);
        return $connection->send(json_encode(['msg' => '登陆成功', 'result' => 1]));
    }

    public function register(TcpConnection $connection, $res)
    {
        $connection->group = $res['group'];
        $connection->is_login = 0;
        $this->service->setGroup($res['group'],$connection);
        return $connection->send(json_encode(['msg' => '注册成功', 'result' => 1]));

    }

    public function ping(TcpConnection $connection, $res)
    {
        return $connection->send('pong');
    }

    private function getMemberId($read_data)
    {
        $member_data = '';

        if (!empty($read_data)) {
            preg_match_all('/yunzshop_([\w]+[^|]*|)/', $read_data, $name_matches);
            preg_match_all('/(a:[\w]+[^}]*})/', $read_data, $value_matches);

            if (!empty($name_matches)) {
                foreach ($name_matches[0] as $key => $val) {
                    if ($val == 'yunzshop_member_id') {
                        $member_data = $val . '|' . $value_matches[0][$key];
                    }
                }
            }
        }
        $member_id = unserialize(explode('|', $member_data)[1])['data'];

        return $member_id;
    }

}
