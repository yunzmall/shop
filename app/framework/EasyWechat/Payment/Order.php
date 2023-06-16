<?php

namespace app\framework\EasyWechat\Payment;

use EasyWeChat\Payment\Order\Client as BaseClient;
use EasyWeChat\Kernel\Support;


class Order extends BaseClient
{


    public function microPay(array $params, $isContract = false)
    {

        $params['spbill_create_ip'] = Support\get_server_ip();
        $params['appid'] = $this->app['config']->app_id;
        $columns = ['appid', 'mch_id', 'device_info', 'nonce_str', 'sign', 'sign_type', 'body',
            'detail', 'attach', 'out_trade_no', 'total_fee', 'fee_type', 'spbill_create_ip',
            'goods_tag', 'limit_pay', 'time_start', 'time_expire', 'receipt', 'auth_code',
            'profit_sharing', 'scene_info'
        ];
        foreach ($params as $k => $v) {
            if (!in_array($k, $columns)) {
                unset($params[$k]);
            }
        }
//        $params['notify_url'] = $params['notify_url'] ?? $this->app['config']['notify_url'];


//        if (empty($params['spbill_create_ip'])) {
//            $params['spbill_create_ip'] = ('NATIVE' === $params['trade_type']) ? Support\get_server_ip() : Support\get_client_ip();
//        }
//
//        $params['appid'] = $this->app['config']->app_id;
//        $params['notify_url'] = $params['notify_url'] ?? $this->app['config']['notify_url'];
//
//        if ($isContract) {
//            $params['contract_appid'] = $this->app['config']['app_id'];
//            $params['contract_mchid'] = $this->app['config']['mch_id'];
//            $params['request_serial'] = $params['request_serial'] ?? time();
//            $params['contract_notify_url'] = $params['contract_notify_url'] ?? $this->app['config']['contract_notify_url'];
//
//            return $this->request($this->wrap('pay/contractorder'), $params);
//        }
//
//        return $this->request('https://api.mch.weixin.qq.com/pay/micropay',$params);
        return $this->request($this->wrap('pay/micropay'), $params);
    }


}
