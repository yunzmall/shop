<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/9
 * Time: 下午5:26
 */

namespace app\backend\modules\supervisord\controllers;

use app\backend\modules\supervisord\services\Supervisor;
use app\common\components\BaseController;
use app\common\facades\SiteSetting;
use app\common\helpers\Cache;
use app\common\helpers\Url;
use app\common\facades\Setting;
use app\host\HostManager;
use Illuminate\Support\Facades\Redis;
use Predis\Connection\ConnectionException;


class SupervisordController extends BaseController
{
    private $supervisor = null;

    public function preAction()
    {
        parent::preAction();
        $this->supervisor = app('supervisor');
        $this->supervisor->setTimeout(5000);  // microseconds
    }

    /**
     * 商城设置
     * @return mixed
     */
    public function index()
    {
        //print_r($supervisor->getState());
        //$allProcessInfo = $this->supervisor->getAllProcessInfo();
        //$allProcessInfo = $supervisor->stopProcess("dev1-worker:dev1-worker_01");
        //$allProcessInfo = $supervisor->readLog(0);
        //$allProcessInfo = $supervisor->logMessage();
        //dd($allProcessInfo);
        $supervisord = SiteSetting::get('supervisor');
        return view('supervisor.index', [
            'service_type' => $supervisord['service_type'] ?: 0
        ])->render();

    }

    public function store()
    {
        $setting = request()->input('setting');

        if ($setting) {

            $setting['address']['ip'] = $setting['address']['ip'] ? trim($setting['address']['ip']) : 'http://127.0.0.1';

            SiteSetting::set('supervisor', $setting);
            return $this->successJson("设置保存成功", Url::absoluteWeb('supervisord.supervisord.store'));
        }
        $supervisord = SiteSetting::get('supervisor');
        $data['address']['ip'] = 'http://127.0.0.1';
        $supervisord['address']['ip'] ?: SiteSetting::set('supervisor', $data);
        return view('supervisor.store', [
            'setting' => json_encode($supervisord)
        ])->render();
    }

    public function process()
    {
        //print_r($supervisor->getState());
        $allProcessInfo = $this->supervisor->getAllProcessInfo();
        $state = $this->supervisor->getState();
        // dd($state);
        foreach ($allProcessInfo as $host => &$value) {
            foreach ($value->val as $key => &$val) {
                $val['cstate'] = false;
                // echo $val;
            }
        }
        $current_time = time();
        $queue_hearteat = [
            'daemon' => $this->daemonStatus(),
            'cron' => \app\backend\modules\survey\models\CronHeartbeat::getLog($current_time),
            'job' => \app\backend\modules\survey\models\JobHeartbeat::getLog($current_time),
            'redis' => $this->getRedisStatus()
        ];
        return json_encode([
            'process' => $allProcessInfo,
            'state' => $state,
            'queue_hearteat' => $queue_hearteat,
            'queue_hearteat_icon' => 'icon-fontclass-deng',
        ]);
    }

    public function showlog()
    {
        $process = \YunShop::request()->process;
        $hostname = \YunShop::request()->hostname;
        $this->supervisor->setCurrentHostname($hostname);
        $result = $this->supervisor->tailProcessStdoutLog($process, 1, 100000);
        $this->supervisor->setCurrentHostname();
        //取当前hostname的数据
        $result = $result[$hostname];
        return json_encode($result);

    }

    public function clearlog()
    {
        $process = \YunShop::request()->process;
        $hostname = \YunShop::request()->hostname;
        $this->supervisor->setCurrentHostname($hostname);
        $result = $this->supervisor->clearProcessLogs($process);
        $this->supervisor->setCurrentHostname();
        $result = $result[$hostname];
        return json_encode($result);

    }

    public function stop()
    {
        $process = \YunShop::request()->process;
        $hostname = \YunShop::request()->hostname;
        $this->supervisor->setCurrentHostname($hostname);
        $result = $this->supervisor->stopProcess($process);
        $this->supervisor->setCurrentHostname();
        $result = $result[$hostname];
        return json_encode($result);
    }

    public function stopAll()
    {
        $result = $this->supervisor->stopAllProcesses();
        return json_encode($result);
    }

    public function start()
    {
        $process = \YunShop::request()->process;
        $hostname = \YunShop::request()->hostname;
        $this->supervisor->setCurrentHostname($hostname);
        $result = $this->supervisor->startProcess($process);
        $this->supervisor->setCurrentHostname();
        $result = $result[$hostname];
        return json_encode($result);

    }

    public function startAll()
    {
        (new HostManager())->restart();
        $result = $this->supervisor->startAllProcesses();
        return json_encode($result);

    }

    public function restart()
    {
        (new HostManager())->restart();
        $result = $this->supervisor->restart();
        return json_encode($result);
    }

    private function daemonStatus()
    {
        $all_status = app('supervisor')->getState();
        $queue_status = 'green';
        $msg = '正常';
        $title = '';
        if (!function_exists('stream_socket_server')) {
            return array('queue_status' => 'yellow', 'msg' => '请解禁stream_socket_server函数');
        }
        foreach ($all_status as $hostname => $status) {
            $code = '正常';
            if ($status->val['statecode'] != 1) {
                $queue_status = 'not_running';
                $msg = $code = '异常';
            }
            $title .= '服务器' . $hostname . "：$code\r\n";
        }
        if (count($all_status) == 1) {
            $title = $msg;
        }
        return array('queue_status' => $queue_status, 'msg' => $msg, 'title' => $title);
    }

    /**
     * @return array
     *   uninstall redis未安装
     *   unexecute redis未执行
     */
    private function getRedisStatus()
    {
        try {
            if (!class_exists('Redis') || !class_exists('Predis\Client')) {
                return array('queue_status' => 'uninstall', 'msg' => 'Redis组件未安装');
            }
            $ping = \Illuminate\Support\Facades\Redis::ping();
            $res = strpos($ping, 'PONG') || $ping == true;
            if ($res !== false) {
                return array('queue_status' => 'green', 'msg' => '正常');
            } else {
                return array('queue_status' => 'unconnection', 'msg' => 'ping失败');
            }
        } catch (ConnectionException $exception) {
            return array('queue_status' => 'unconnection', 'msg' => '连接失败');
        } catch (\Exception $exception) {
            return array('queue_status' => 'unexecute', 'msg' => '无法使用');
        }
    }}