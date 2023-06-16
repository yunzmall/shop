<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/10/14 下午10:23
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 ****************************************************************/

namespace app\backend\modules\finance\controllers;


use app\common\components\BaseController;
use app\common\models\Withdraw;

class WithdrawStatisticsController extends BaseController
{

    private $withdrawModel;

    public function preAction()
    {
        parent::preAction();
        $this->withdrawModel = new Withdraw();

    }

    public function index()
    {
        if (request()->ajax()) {
            $search = \YunShop::request()->search;
            $has_time = Is_numeric($search['time']['start']) && Is_numeric($search['time']['end']);
            if ($has_time) {
                $time_data[] = ['start_time' => $search['time']['start'] / 1000, 'end_time' => $search['time']['end'] / 1000];
            } else {
                $time_data = $this->getDate();
            }

            $data = [];
            $amount = 0;
            foreach ($time_data as $key => $item) {
                $records = $this->getWithdrawAmounts($has_time, $item);
                $amount += $records['amount'];
                $data[] = $records;
            }
            return $this->successJson('ok', [
                'data' => $data,
                'high_light_open' => app('plugins')->isEnabled('high-light') ? 1 : 0,
                'amount' => $amount
            ]);
        }

        return view('finance.withdraw.withdraw-statistics')->render();
    }


    private function getDate($begin_today = '', $end_today = '', $length = 6)
    {

        $begin_today = $begin_today ?: mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        $end_today = $end_today ?: mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

        $data = [];
        for ($i = 0; $i <= $length; $i++) {
            $data[] = ['start_time' => $begin_today, 'end_time' => $end_today];
            $begin_today = $begin_today - 86400;
            $end_today = $end_today - 86400;
        }

        return $data;
    }

    private function getWithdrawAmounts($has_time, $item)
    {
        $data = [
            'balance' => $this->withdrawModel->uniacid()->where('pay_way', 'balance')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
            'wechat' => $this->withdrawModel->uniacid()->where('pay_way', 'wechat')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
            'alipay' => $this->withdrawModel->uniacid()->where('pay_way', 'alipay')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
            'manual' => $this->withdrawModel->uniacid()->where('pay_way', 'manual')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
            'converge_pay' => $this->withdrawModel->uniacid()->where('pay_way', 'converge_pay')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
        ];

        if (app('plugins')->isEnabled('high-light')) {
            $high = [
                'high_light_wechat' => $this->withdrawModel->uniacid()->where('pay_way', 'high_light_wechat')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
                'high_light_alipay' => $this->withdrawModel->uniacid()->where('pay_way', 'high_light_alipay')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
                'high_light_bank' => $this->withdrawModel->uniacid()->where('pay_way', 'high_light_bank')->whereBetween('created_at', [$item['start_time'], $item['end_time']])->sum('amounts'),
            ];
            $data = array_merge($data, $high);
        }
        $data['amount'] = 0;
        foreach ($data as $key => $i) {
            $data['amount'] = bcadd($data['amount'], $i, 2);
        }

        $data['time'] = $has_time ? '时间段搜索' : date('Y-m-d', $item['start_time']);
        return $data;
    }


}
