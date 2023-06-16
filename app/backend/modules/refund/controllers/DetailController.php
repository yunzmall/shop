<?php

namespace app\backend\modules\refund\controllers;

use app\common\exceptions\AppException;
use app\common\models\goods\ReturnAddress;
use Illuminate\Support\Facades\Redis;
use app\common\components\BaseController;
use app\common\models\refund\RefundApply;

/**
 * 退款申请详情
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/13
 * Time: 下午3:04
 */
class DetailController extends  BaseController
{

    protected $refundApply;


    public function consultRecord()
    {
        $order_id = intval(request()->input('order_id'));
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function express()
    {

        //refund.detail.express
        $refund_value = request()->input('refund_value');

        if ($refund_value == 20) {
            return $this->returnLogistics();
        } elseif ($refund_value == 30) {
            return $this->resendLogistics();
        }

        return $this->errorJson('不存在物流');
    }


    public function returnLogistics()
    {
        $this->refundApply = $refundApply = RefundApply::find(request()->input('refund_id'));
        if(!$refundApply){
            throw new AppException('未找到该售后信息');
        }

        if(!$refundApply->returnExpress) {
            throw new AppException('未找到该售后快递单号');
        }

        $cacheKey = 'backend_refundExpressId_'.$refundApply->id.'_' . $refundApply->returnExpress->express_sn;


        $result = Redis::get($cacheKey);

        if ($result) {
            $result = json_decode($result, true);
            return $this->successJson('用户发货物流信息Cache', $result);
        }

        switch ($refundApply->returnExpress->way_id) {
            case 1:
                if(app('plugins')->isEnabled('jd-take-parts')) {
                    $dispatch = \Yunshop\JdTakeParts\services\RefundService::receiveTraceGet($refundApply);
                    break;
                }
            default:
                $dispatch = $this->getLogistics($refundApply->returnExpress);
        }
        $dispatch['goods'] = [];
        $dispatch['thumb'] = $refundApply->returnExpress->images;

        $result[] = $dispatch;
        Redis::setex($cacheKey, 120, json_encode($result));

        return $this->successJson('用户发货物流信息',$result);

    }

    public function resendLogistics()
    {
        $this->refundApply = $refundApply = RefundApply::find(request()->input('refund_id'));
        if(!$refundApply){
            throw new AppException('未找到该售后信息');
        }

        if($refundApply->hasManyResendExpress->isEmpty()) {
            throw new AppException('未找到该售后快递');
        }


        $cacheKey = 'backend_refundExpressId_'.$refundApply->id.'_' . $refundApply->hasManyResendExpress->count();

        $result = Redis::get($cacheKey);
        if ($result) {
            $result = json_decode($result, true);
            return $this->successJson('商户发货物流信息Cache', $result);
        }

        $result = [];

        foreach ($refundApply->hasManyResendExpress as $resendExpress) {
            $dispatch = $this->getLogistics($resendExpress);
            $dispatch['goods'] = $resendExpress->pack_goods?: [];
            $dispatch['thumb'] = $resendExpress->pack_goods?$resendExpress->pack_goods[0]['goods_thumb'] : '';
            $result[] = $dispatch;
        }

        Redis::setex($cacheKey, 120, json_encode($result));

        return $this->successJson('商户发货物流信息', $result);

    }


    protected function getLogistics($expressModel)
    {
//        return $this->testData($expressModel);
        //买家寄回物流信息

        $phoneLastFour = '';
        if ($expressModel->express_code == 'SF') {
            $returnAddress = ReturnAddress::find($this->refundApply->refund_address);
            if (empty($returnAddress->mobile)) {
                throw new AppException('SF查询物流，联系电话不能为空');
            }
            $phoneLastFour = substr($returnAddress->mobile,-4);
        }

        $express = (new \app\common\models\order\Express())->getExpress($expressModel->express_code, $expressModel->express_sn,$phoneLastFour);

        $dispatch['express_sn'] = $expressModel->express_sn;
        $dispatch['company_name'] = $expressModel->express_company_name;
        $dispatch['data'] = $express['data'];
        $dispatch['thumb'] = '';
        $dispatch['tel'] = '95533';
        $dispatch['status_name'] = $express['status_name'];

        return $dispatch;
    }


    protected function testData($expressModel)
    {
        $dispatch['express_sn'] = $expressModel->express_sn;
        $dispatch['company_name'] = $expressModel->express_company_name;

        $data = [];
        for ($i = rand(8,20);$i > 1; $i--) {

            $data[] = [
                'context' => '物流信息到达:'.$i,
                'ftime'   => '202111231+'.$i,
            ];
        }
        $dispatch['data'] = $data;

        $dispatch['thumb'] = '';
        $dispatch['tel'] = '95533';
        $dispatch['status_name'] = '测试状态：'.rand(0,100);


        return $dispatch;
    }
}