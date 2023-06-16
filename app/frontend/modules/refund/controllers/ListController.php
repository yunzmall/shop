<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/12
 * Time: 下午7:40
 */

namespace app\frontend\modules\refund\controllers;


use app\common\components\ApiController;
use app\common\events\order\OrderRefundFrontedListEvent;
use app\frontend\modules\refund\models\RefundApply;

class ListController extends ApiController
{
//    public function index(\Illuminate\Http\Request $request)
//    {
//        $this->validate([
//            'pagesize' => 'sometimes|filled|integer',
//            'page' => 'sometimes|filled|integer',
//        ]);
//        $query = RefundApply::defaults();
//        event($event = new OrderRefundFrontedListEvent($query));
//        $refunds = $event->getQuery()->paginate($request->query('pagesize', '20'));
//        return $this->successJson('成功', $refunds);
//    }


    public function index()
    {
        $refundModel = $this->refundModel();

        return $this->successJson('成功', $this->getData($refundModel));
    }

    public function wait()
    {
        $refundModel = $this->refundModel()
            ->where('status', '>', RefundApply::REJECT)
            ->where('status', '<', RefundApply::COMPLETE);

        return $this->successJson('成功', $this->getData($refundModel));
    }

    public function complete()
    {
        $refundModel = $this->refundModel()
            ->where('status', '>=', RefundApply::COMPLETE);

        return $this->successJson('成功', $this->getData($refundModel));
    }

    public function cancel()
    {
        $refundModel = $this->refundModel()
            ->where('status', '<', RefundApply::WAIT_CHECK);

        return $this->successJson('成功', $this->getData($refundModel));
    }

    /**
     * @return RefundApply
     */
    protected function refundModel()
    {
        return RefundApply::uniacid();
    }

    /**
     * @param \app\framework\Database\Eloquent\Builder $refundModel
     * @return mixed
     */
    protected function getData($refundModel)
    {
        $page_size = request()->input('page_size',20);

        $search = [
            'sn' => request()->input('sn'),
            'refund_id' => intval(request()->input('refund_id')),
            'order_goods_id' => intval(request()->input('order_goods_id')),
        ];

        $refundModel->frontendSearch($search);

//        $refundModel->whereIn('id',[4,5]);

        event($event = new OrderRefundFrontedListEvent($refundModel));
        $refundList = $event->getQuery()->paginate($page_size);

        return $refundList;
    }
}