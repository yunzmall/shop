<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/7 下午2:15
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 ****************************************************************/

namespace app\frontend\modules\finance\controllers;


use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\services\password\PasswordService;
use app\frontend\models\Member;
use app\frontend\modules\finance\models\PointLog;

class PointPageController extends ApiController
{
    /**
     * @var Member
     */
    private $memberModel;

    public function index()
    {
        $this->getMemberInfo();

        if (!$this->memberModel) return $this->errorJson('未获取到会员信息');

        $result = $this->apiData();
        if (!versionCompare('1.1.138') || !miniVersionCompare('1.1.138')) {
            $result = $this->oldApiData();
        }
        return $this->successJson('ok', $result);
    }

    /**
     * 1.1.137版本及之前接口
     * @return array
     */
    private function oldApiData()
    {
        $setting = Setting::get('point.set');
        $return_data =  [
            'credit1'       => $this->memberModel->credit1,
            'transfer'      => $this->getTransferStatus(),
            'is_transfer_integral' => $this->getTransferIntegralStatus(),//转化消费积分
            'activity'      => $this->getActivityStatus(),
            'rate'          => $this->getRateSet(),
            'lan_plugin'    => $this->lanPlugin(),
            'lan_name'      => $this->lanName(),
            'has_password'  => $this->hasPassword(),
            'need_password' => $this->needPassword(),
            'exchange_to_love_by_member' => app('plugins')->isEnabled('love') && $setting['exchange_to_love_by_member'] ? 1 : 0,
            'transfer_integral' => floatval($setting['transfer_integral']) ? floatval($setting['transfer_integral']) + 0 : 1,
            'transfer_integral_love' => floatval($setting['transfer_integral_love']) ? floatval($setting['transfer_integral_love']) + 0 : 1,
        ];
        if (app('plugins')->isEnabled('point-exchange')) {
            if (($setting = \Yunshop\PointExchange\services\SettingService::getSetting())['open_state']) {
                $return_data['point_exchange'] = [
                    'name' => '积分通转兑',
                    'out_rate' => 1,
                    'in_rate' => bcdiv($setting['exchange_percent'], 100, 2),
                ];
            }
        }
        return $return_data;
    }


    /**
     * 1.1.138版本接口
     * @return array
     */
    private function apiData()
    {
        $setting = Setting::get('point.set');
        $arr = [
            'credit1' => $this->memberModel->credit1,
            'transfer' => $this->getTransferStatus(),
            'is_transfer_integral' => $this->getTransferIntegralStatus(),//转化消费积分
            'rate' => $this->getRateSet(),
            'has_password' => $this->hasPassword(),
            'need_password' => $this->needPassword(),
            'other' => [],
            'latest_record'=>(new PointLog())->getLatestRecord($this->memberModel->uid),
            'point_task_switch'=>$this->getPointTaskStatus(),
            'transfer_integral' => floatval($setting['transfer_integral']) ? floatval($setting['transfer_integral']) + 0 : 1,
            'transfer_integral_love' => floatval($setting['transfer_integral_love']) ? floatval($setting['transfer_integral_love']) + 0 : 1,
        ];
        $pointName=Setting::get('shop.shop')['credit1'] ?: "积分";
        if($this->getActivityStatus()){
            $arr['other']['activity']=
                [
                    'img'=>'https://mini-app-img-1251768088.cos.ap-guangzhou.myqcloud.com/images%2Fintegral%2Fimage10.png',
                    'name'=>$pointName.'活动'
                ];
        }
        if($this->lanPlugin()&&request()->type!=2){
            $arr['other']['lan_plugin']=[
                'name'=>$this->lanName(),
                'img'=>'https://mini-app-img-1251768088.cos.ap-guangzhou.myqcloud.com/images%2Fintegral%2Fimage7.png'
            ];
        }

        if($this->getTransferIntegralStatus()){
            $integralName = \Yunshop\Integral\Common\Services\SetService::getIntegralName();
            $arr['other']['transfer_integral']=[
                'img'=>'https://mini-app-img-1251768088.cos.ap-guangzhou.myqcloud.com/images%2Fintegral%2Fimage8.png',
                'name'=>$pointName.'转化'.$integralName
            ];
        }
        if(app('plugins')->isEnabled('love')&&$setting['exchange_to_love_by_member'] ? 1 : 0){
            $name=$pointName.'转'.(Setting::get('love.name')?Setting::get('love.name'):'爱心值');
            $arr['other']['exchange_to_love_by_member']=[
                'img'=>'https://mini-app-img-1251768088.cos.ap-guangzhou.myqcloud.com/images%2Fintegral%2Fimage9.png',
                'name'=>$name
            ];
        }
        if (app('plugins')->isEnabled('point-exchange')) {
            if (($setting = \Yunshop\PointExchange\services\SettingService::getSetting())['open_state']) {
                $arr['other']['point_exchange'] = [
                    'img' => 'https://mini-app-img-1251768088.cos.ap-guangzhou.myqcloud.com/plugin/distribution.png',
                    'name' => '积分通转兑',
                ];
            }
        }
        if(empty($arr['other'])){
            $arr['other']=false;
        }
        return $arr;
    }

    private function lanPlugin()
    {
        return (int)app('plugins')->isEnabled('point_exchange')&&\Setting::get('plugin.point-exchange.is_open_point_exchange');
    }

    private function lanName()
    {
        $set = Setting::get('plugin.point-exchange');

        return empty($set['plugin_name']) ? "蓝牛积分" : $set['plugin_name'];
    }

    private function hasPassword()
    {
        return $this->memberModel->yzMember->hasPayPassword();
    }

    private function needPassword()
    {
        return (new PasswordService())->isNeed('point', 'transfer');
    }

    // 获取积分任务状态
    private function getPointTaskStatus()
    {
        return app('plugins')->isEnabled('point-task')&&Setting::get('plugin.point-task.point_task_enable');
    }

    private function getTransferStatus()
    {
        return Setting::get('point.set.point_transfer') ? true : false;
    }
    private function getTransferIntegralStatus()
    {
        return app('plugins')->isEnabled('integral') && Setting::get('point.set.is_transfer_integral') ? true : false;
    }

    private function getActivityStatus()
    {
        return app('plugins')->isEnabled('point-activity');
    }

    private function getMemberInfo()
    {
        return $this->memberModel = Member::where('uid', $this->getMemberId())->first();
    }

    private function getMemberId()
    {
        return \YunShop::app()->getMemberId();
    }

    private function getRateSet()
    {
        return intval(Setting::get('point.set.point_transfer_poundage')) / 100 ?: 0;
    }
}
