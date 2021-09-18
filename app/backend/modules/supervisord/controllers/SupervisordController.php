<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
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
use Illuminate\Support\Facades\Redis;


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
        return view('supervisor.index', [
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

        $supervisord['address']['ip'] ?: SiteSetting::set('supervisor', $supervisord['address']['ip'] = 'http://127.0.0.1');
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
        return json_encode([
            'process' => $allProcessInfo,
            'state'   => $state
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
        $result = $this->supervisor->startAllProcesses();
        return json_encode($result);

    }

    public function restart()
    {
        $result = $this->supervisor->restart();
        return json_encode($result);
    }

}