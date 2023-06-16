<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/13
 * Time: 下午2:00
 */

namespace app\frontend\modules\refund\controllers;

use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\models\refund\RefundProcessLog;
use app\frontend\modules\refund\models\RefundApply;
use app\frontend\modules\refund\services\RefundService;

class DetailController extends ApiController
{
    public function index(\Illuminate\Http\Request $request){
        $this->validate([
            'refund_id' => 'required|integer',
        ]);
        $refundApply = RefundApply::detail()->find($request->query('refund_id'));
        if(!isset($refundApply)){
            throw new AppException('未找到该退款申请');
        }

        $refundApply->resend_express_id = 0;
        if ($refundApply->refund_type == RefundApply::REFUND_TYPE_EXCHANGE_GOODS && $refundApply->hasManyResendExpress->count() == 1) {
            $refundApply->resend_express_id = $refundApply->hasManyResendExpress->first()->id;
        }

        $refundApply->remark = $refundApply->remark ?: '';

        //判断是门店还是供应商
        $plugin = RefundApply::getIsPlugin($refundApply->order_id);
        if($plugin->is_plugin) {
            $refundApply->is_plugin = $plugin->is_plugin;
            $refundApply->supplier_id = RefundApply::getSupplierId($refundApply->order_id);
        }
        if ($plugin->plugin_id == 32) {
            $refundApply->plugin_id = $plugin->plugin_id;
            $refundApply->store_id = RefundApply::getStoreId($refundApply->order_id);
        }

        $refundApply->reject_time = $refundApply->reject_time ? date('Y-m-d H:i:s') : '';

        $refundApply['other_data'] = RefundService::getSendBackWayDetailData($refundApply);

        return $this->successJson('成功',$refundApply);
    }


    public function processLog()
    {
        $list = RefundProcessLog::where('refund_id', request()->input('refund_id'))->get();

        return $this->successJson('list', $list);
    }
}