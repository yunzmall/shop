<?php

namespace app\process;


use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

class WebSocket
{

    protected $worker;

    protected $connections = [];

    protected $members = [];

    protected $groups = [];

    protected $handle;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
        $this->handle = new WebSocketHandle($this);
    }

    public function setMember($member_id,$group,TcpConnection $connection)
    {
        $this->members[$group][$member_id] = $connection;
    }

    public function setGroup($group,TcpConnection $connection)
    {
        $this->groups[$group][$connection->id] = $connection;
    }

    public function onConnect(TcpConnection $connection)
    {
        $this->connections[$connection->id] = $connection;
        $connection->lastMessageTime = time();
    }

    public function onMessage(TcpConnection $connection, $res)
    {
        $connection->lastMessageTime = time();
        $res = json_decode($res, true);
        if (!method_exists($this->handle, $res['type'])) {
            return $connection->send('pong');
        }
        $this->handle->{$res['type']}($connection, $res['data']);
    }


    public function onWorkerStart(Worker $worker)
    {
        var_dump('onWorkerStart');
        // 启动时顺便启动内部通信，并连接监听内部通讯
        $inner_worker = new \app\worker\Worker('Text://127.0.0.1:5678');
        $inner_worker->onMessage = function (TcpConnection $tcpConnection, $data) use ($worker) {
            $data = json_decode($data, true);
            $group = $this->groups[$data['type']];
            if (!empty($data['ids'])) {
                foreach ($data['ids'] as $id) {
                    $connection = $this->members[$data['type']][$id];
                    isset($connection) && $connection->send(json_encode($data['content']));
                }
            } else {
                foreach ($group as $connection) {
                    isset($connection) && $connection->send(json_encode($data['content']));
                }
            }
            if ($data['type'] == 'is_online') {
                $online = [];
                foreach ($this->groups['online_service'] as $connection) {
                    $online[] = $connection->member_id;
                }
                return $tcpConnection->send(json_encode($online));
            }
            $tcpConnection->send('success');
        };
        $inner_worker->listen();

        //心跳监测
        Timer::add(10, function () use (&$worker) {
            $time_now = time();
            foreach ($worker->connections as $connection) {
                if ($time_now - $connection->lastMessageTime > 30) {
                    $connection->close('心跳超时断开');
                }
            }
        });
    }


    public function onClose(TcpConnection $connection)
    {
        unset($this->connections[$connection->id]);
        if (isset($connection->member_id) && isset($connection->group)) {
            unset($this->members[$connection->group][$connection->member_id]);
            unset($this->groups[$connection->group][$connection->id]);
        }
    }
}
