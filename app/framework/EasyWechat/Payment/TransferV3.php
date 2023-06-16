<?php

namespace app\framework\EasyWechat\Payment;

use app\common\facades\Setting;
use app\common\services\wechatApiV3\ApiV3Config;
use EasyWeChat\OfficialAccount\Material\Client as BaseClient;

class TransferV3 extends BaseClient
{
    public function batches(array $params)
    {
        $params['appid'] = $this->app['config']->app_id;
        $columns = [
            'appid', 'out_batch_no', 'batch_name', 'batch_remark', 'total_amount', 'total_num', 'transfer_detail_list',
        ];
        foreach ($params as $k => $v) {
            if (!in_array($k, $columns)) {
                unset($params[$k]);
            }
        }

        $apiV3 = new ApiV3Config($this->getConfig());

        //敏感数据加密
        $is_encrypt = false;
        foreach ($params['transfer_detail_list'] as &$item) {
            if ($item['user_name']) {
                $item['user_name'] = $apiV3->encrypt()->encrypt($item['user_name']);
                $is_encrypt = true;
            }
        }
        unset($item);
        return $apiV3->request()->httpRequest('https://api.mch.weixin.qq.com/v3/transfer/batches',$params,'POST',$is_encrypt);
    }

    public function searchBatch(array $params)
    {
        if (!$params['out_batch_no']) {
            throw new \Exception('商家批次单号必填!');
        }
        $columns = [
            'need_query_detail', 'offset', 'limit', 'detail_status',
        ];
        $params['need_query_detail'] = (bool)$params['need_query_detail'];
        $query = '';
        foreach ($params as $k => $v) {
            if (in_array($k, $columns)) {
                $query .= (($query ? '&':'?') . $k . '=' . $v);
            }
        }
        $url = "https://api.mch.weixin.qq.com/v3/transfer/batches/out-batch-no/".$params['out_batch_no'].$query;
        $apiV3 = new ApiV3Config($this->getConfig());
        return $apiV3->request()->httpRequest($url,[],'GET',false,1);
    }

    public function searchDetails(array $params)
    {
        if (!$params['out_batch_no']) {
            throw new \Exception('商家批次单号必填!');
        }
        if (!$params['out_detail_no']) {
            throw new \Exception('商家明细单号必填!');
        }
        $url = "https://api.mch.weixin.qq.com/v3/transfer/batches/out-batch-no/".$params['out_batch_no']."/details/out-detail-no/".$params['out_detail_no'];
        $apiV3 = new ApiV3Config($this->getConfig());
        return $apiV3->request()->httpRequest($url,[],'GET',false,1);
    }

    private function getConfig()
    {
        $config = [
            'appid' => $this->app['config']->app_id,
            'secret' => $this->app['config']->key,
            'secret_v3' => $this->app['config']->key_v3 ? : '',
            'mchid' => $this->app['config']->mch_id,
            'api_cert_pem' => $this->app['config']->cert_path,
            'api_key_pem' => $this->app['config']->key_path,
        ];
        return $config;
    }
}