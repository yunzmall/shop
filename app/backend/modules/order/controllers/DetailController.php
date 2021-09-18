<?php
/**
 * 订单详情
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/4
 * Time: 上午11:16
 */

namespace app\backend\modules\order\controllers;

use app\backend\modules\member\models\Member;
use app\backend\modules\order\models\Order;
use app\backend\modules\order\models\VueOrder;
use app\backend\modules\refund\models\RefundApply;
use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\models\Goods;
use app\common\models\MemberShopInfo;
use app\common\modules\order\OrderOperationsCollector;
use app\common\services\DivFromService;

class DetailController extends BaseController
{
    public function getMemberButtons()
    {
        $orderStatus = array_keys(app('OrderManager')->setting('status'));
        $buttons = array_map(function ($orderStatus) {
            var_dump($orderStatus);
            $order = Order::where('status', $orderStatus)->orderBy('id', 'desc')->first();
            dump($order->buttonModels);
            dump($order->oldButtonModels);
        }, $orderStatus);
    }

    public function ajax()
    {
        $order = Order::orders()->with(['deductions', 'coupons', 'discounts','orderFees', 'orderServiceFees', 'orderPays' => function ($query) {
            $query->with('payType');
        }, 'hasOnePayType']);
        if (request()->has('id')) {
            $order = $order->find(request('id'));
        }
        if (request()->has('order_sn')) {
            $order = $order->where('order_sn', request('order_sn'))->first();
        }
        if (!$order) {
            throw new AppException('未找到订单');
        }
        if (!empty($order->express)) {
            $express = $order->express->getExpress($order->express->express_code, $order->express->express_sn);
            $dispatch['express_sn'] = $order->express->express_sn;
            $dispatch['company_name'] = $order->express->express_company_name;
            $dispatch['data'] = $express['data'];
            $dispatch['thumb'] = $order->hasManyOrderGoods[0]->thumb;
            $dispatch['tel'] = '95533';
            $dispatch['status_name'] = $express['status_name'];
        }
        return $order->toArray();
    }

    public function express()
    {
//        $express = RefundApply::where('order_id',request('id'))->with('returnExpress')->first();
//        dd($express);

        $order = Order::orders()->with(['deductions', 'coupons', 'discounts','orderFees', 'orderServiceFees', 'orderPays' => function ($query) {
            $query->with('payType');
        }, 'hasOnePayType']);
        if (request()->has('id')) {
            $order = $order->find(request('id'));
        }
        if (request()->has('order_sn')) {
            $order = $order->where('order_sn', request('order_sn'))->first();
        }
        if (!$order) {
            throw new AppException('未找到订单');
        }
//        dd($order->hasOneRefundApply->returnExpress);
        if (!empty($order->hasOneRefundApply->returnExpress)) {

            $express = $order->express->getExpress($order->hasOneRefundApply->returnExpress->express_code, $order->hasOneRefundApply->returnExpress->express_sn);

            $dispatch['express_sn'] = $order->hasOneRefundApply->returnExpress->express_sn;
            $dispatch['company_name'] = $order->hasOneRefundApply->returnExpress->express_company_name;
            $dispatch['data'] = $express['data'];
            $dispatch['thumb'] = $order->hasManyOrderGoods[0]->thumb;
            $dispatch['tel'] = '95533';
            $dispatch['status_name'] = $express['status_name'];
        }

        return $this->errorJson('查询成功',$dispatch);
    }

    /**
     * @param \Request $request
     * @return string
     * @throws AppException
     * @throws \Throwable
     */
    public function index(\Illuminate\Http\Request $request)
    {

        $order = Order::orders()->with(['deductions', 'coupons', 'discounts','orderFees', 'orderServiceFees', 'orderInvoice', 'orderPays' => function ($query) {
            $query->with('payType');
        }, 'hasOnePayType','hasOneExpeditingDelivery','expressmany'=>function($query){
            $query->with(['ordergoods'=>function($q){
                $q->select('id','goods_id','thumb','title','goods_option_title','goods_sn','goods_market_price','payment_amount','total','order_express_id');
            }]);
        }]);
        if (request()->has('id')) {
            $order = $order->find(request('id'));
        }
        if (request()->has('order_sn')) {
            $order = $order->where('order_sn', request('order_sn'))->first();
        }

        if (!$order) {
            throw new AppException('未找到订单');
        }
//dd($order->toArray());
        $dispatch = [];
        if (!$order->expressmany->isEmpty() && $order->status>1) {

            //兼容以前的 因为批量发货并不会把快递id赋值给订单商品
            if($order->is_all_send_goods==0){
                $express = $order->express->getExpress($order->express->express_code, $order->express->express_sn);
                $dispatch[0]['order_express_id'] = $order->expressmany[0]->id;
                $dispatch[0]['express_sn'] =  $order->expressmany[0]->express_sn;
                $dispatch[0]['company_name'] = $order->expressmany[0]->express_company_name;
                $dispatch[0]['data'] = $express['data'];
                $dispatch[0]['thumb'] = $order->hasManyOrderGoods[0]->thumb;
                $dispatch[0]['tel'] = '95533';
                $dispatch[0]['status_name'] = $express['status_name'];
                $dispatch[0]['count'] = count($order->hasManyOrderGoods);
                $dispatch[0]['goods'] = $order->hasManyOrderGoods;
            }else{
                $expressmany = $order->expressmany;
                foreach ($expressmany as $k=>$v){
                    $express = $order->express->getExpress($v->express_code, $v->express_sn);
                    $dispatch[$k]['order_express_id'] = $v->id;
                    $dispatch[$k]['express_sn'] = $v->express_sn;
                    $dispatch[$k]['company_name'] = $v->express_company_name;
                    $dispatch[$k]['data'] = $express['data'];
                    $dispatch[$k]['thumb'] = $v->ordergoods[0]->thumb;
                    $dispatch[$k]['tel'] = '95533';
                    $dispatch[$k]['status_name'] = $express['status_name'];
                    $dispatch[$k]['count'] = count($v['ordergoods']);
                    $dispatch[$k]['goods'] = $v['ordergoods'];
                }
            }


        }
        if ($order->orderInvoice) {
            $order->invoice_type= $order->orderInvoice->invoice_type;
            $order->email = $order->orderInvoice->email;
            $order->rise_type = $order->orderInvoice->rise_type;
            $order->collect_name = $order->orderInvoice->collect_name;
            $order->company_number = $order->orderInvoice->company_number;
            $order->invoice = $order->orderInvoice->invoice;
        }


        $trade = \Setting::get('shop.trade');

        foreach ($order['hasManyOrderGoods'] as $key => $order_goods){
            $order['hasManyOrderGoods'][$key]['goods_price'] = bcdiv($order_goods['goods_price'],$order_goods['total'],2);
            $order['hasManyOrderGoods'][$key]['goods_market_price'] = bcdiv($order_goods['goods_market_price'],$order_goods['total'],2);
            $order['hasManyOrderGoods'][$key]['goods_cost_price'] = bcdiv($order_goods['goods_cost_price'],$order_goods['total'],2);
        }

        $order = $order ? $order->toArray() : [];
        if (empty($order['belongs_to_member'])) {
            $yz_member = MemberShopInfo::withTrashed()->where('member_id', $order['uid'])->first();
        }
        //因增加多包裹功能所以is_zhu就是多包裹功能所使用，原因是部分插件直接调用主程序的订单详情页面所以不能在原页面上直接更改
        return view('order.detail', [
            'order' => $order,
            'is_zhu'=>1,
            'yz_member' => $yz_member,
            'invoice_set'=>$trade['invoice'],
            'dispatch' => $dispatch,
            'div_from' => $this->getDivFrom($order),
            'var' => \YunShop::app()->get(),
            'ops' => 'order.ops',
            'edit_goods' => 'goods.goods.edit'
        ])->render();
    }

    protected function getDivFrom($order)
    {
        if (!$order || !$order['has_many_order_goods']) {
            return ['status' => false];
        }
        $goods_ids = [];
        foreach ($order['has_many_order_goods'] as $key => $goods) {
            $goods_ids[] = $goods['goods_id'];
        }

        $memberInfo = Member::select('realname', 'idcard')->where('uid', $order['uid'])->first();

        $result['status'] = DivFromService::isDisplay($goods_ids);
        $result['member_name'] = $order['has_many_member_certified']['realname'] ?: $memberInfo->realname;
        $result['member_card'] = $order['has_many_member_certified']['idcard'] ?: $memberInfo->idcard;

        return $result;
    }
    public function vueIndex()
    {

        $order_id = intval(request()->input('id'));

        $order_sn = request()->input('order_sn', '');

        if (empty($order_id) && empty($order_sn)) {
            throw new AppException('订单参数为空');
        }

        if (empty($order_id)) {
            $order_id = VueOrder::uniacid()->where('order_sn',$order_sn)->value('id');

            request()->offsetSet('id', $order_id);
        }

        $data['requestInputs'] = request()->input();


        return view('order.vue-detail', ['data'=> json_encode($data)])->render();
    }

    //新订单详情接口
    public function getData()
    {

        $order_id = intval(request()->input('id'));

        if (empty($order_id)) {
            throw new AppException('订单参数为空');
        }

        $order = VueOrder::detailOrders()->with([
            'deductions',
            'coupons',
            'discounts',
            'orderFees',
            'orderServiceFees',
            'orderInvoice',
            'orderPays' => function ($query) {
                $query->with('payType');
            },
            'hasOnePayType',
            'hasOneExpeditingDelivery',
            'hasManyMemberCertified',
        ])
            ->find($order_id);


        $order->orderSteps =  (new \app\backend\modules\order\steps\OrderStatusStepManager($order))->getStepItems();

        if (!$order) {
            throw new AppException('未找到订单');
        }
        $dispatch = [];
        if (!$order->expressmany->isEmpty()) {
            //兼容以前的 因为批量发货并不会把快递id赋值给订单商品
            if($order->is_all_send_goods==0){
                $express = $order->express->getExpress($order->express->express_code, $order->express->express_sn);
                $dispatch[0]['order_express_id'] = $order->expressmany[0]->id;
                $dispatch[0]['express_sn'] =  $order->expressmany[0]->express_sn;
                $dispatch[0]['company_name'] = $order->expressmany[0]->express_company_name;
                $dispatch[0]['data'] = $express['data'];
                $dispatch[0]['thumb'] = $order->hasManyOrderGoods[0]->thumb;
                $dispatch[0]['tel'] = '95533';
                $dispatch[0]['status_name'] = $express['status_name'];
                $dispatch[0]['count'] = count($order->hasManyOrderGoods);
                $dispatch[0]['goods'] = $order->hasManyOrderGoods;
            }else{
                $expressmany = $order->expressmany;
                foreach ($expressmany as $k=>$v){
                    $express = $order->express->getExpress($v->express_code, $v->express_sn);
                    $dispatch[$k]['order_express_id'] = $v->id;
                    $dispatch[$k]['express_sn'] = $v->express_sn;
                    $dispatch[$k]['company_name'] = $v->express_company_name;
                    $dispatch[$k]['data'] = $express['data'];
                    $dispatch[$k]['thumb'] = $v->ordergoods[0]->thumb;
                    $dispatch[$k]['tel'] = '95533';
                    $dispatch[$k]['status_name'] = $express['status_name'];
                    $dispatch[$k]['count'] = count($v['ordergoods']);
                    $dispatch[$k]['goods'] = $v['ordergoods'];
                }
            }


        }


        if ($order->orderInvoice) {
            $order->invoice_type= $order->orderInvoice->invoice_type;
            $order->email = $order->orderInvoice->email;
            $order->rise_type = $order->orderInvoice->rise_type;
            $order->collect_name = $order->orderInvoice->collect_name;
            $order->company_number = $order->orderInvoice->company_number;
            $order->invoice = yz_tomedia($order->orderInvoice->invoice);
        } else {
            $order->invoice = yz_tomedia($order->invoice);
        }

        if ($order->hasOneRefundApply) {
            $refundApply = $order->hasOneRefundApply;
            $refundApply->backend_button_models = (new \app\backend\modules\refund\services\BackendRefundButtonService($refundApply))->getButtonModels();
            $refundApply->refundSteps =  (new \app\backend\modules\refund\services\steps\RefundStatusStepManager($refundApply))->getStepItems();
//            dd($refundApply);
        }

//dd($order->hasOneRefundApply->toArray());

        $order->hasManyOrderGoods->map(function ($order_goods) {
            $order_goods->goods_price =  bcdiv($order_goods->goods_price,$order_goods->total,2);
            $order_goods->goods_market_price = bcdiv($order_goods->goods_market_price,$order_goods->total,2);
            $order_goods->goods_cost_price = bcdiv($order_goods->goods_cost_price,$order_goods->total,2);

        });


        $order = $order ? $order->toArray() : [];
        if (empty($order['belongs_to_member'])) {
            $yz_member = MemberShopInfo::withTrashed()->where('member_id', $order['uid'])->first();
        }

//dd($refundApply->create_time);
        $data = [
            'order' => $order,
            'refundApply' => $refundApply,
            'dispatch' => $dispatch?:[],
            'yz_member' => $yz_member?:[],
            'div_from' => $this->getDivFrom($order),
            'expressCompanies' =>  \app\common\repositories\ExpressCompany::create()->all(),
        ];
//        dd($data);

        return $this->successJson('detail', $data);
    }


    //退款物流信息
    public function refundExpress()
    {

        $order_id = intval(request()->input('order_id'));

        $refund_value = request()->input('refund_value');

        $order = VueOrder::uniacid()->with([
            'hasOneRefundApply' => function($query) {
                return $query->with('returnExpress')->with('resendExpress');
            }])->where('id',$order_id)->first();

        if (!$order) {
            throw new AppException('未找到订单');
        }

        if (is_null($order->hasOneRefundApply->returnExpress) && is_null($order->hasOneRefundApply->resendExpress)) {
            throw new AppException('物流信息为空');
        }

        $dispatch = [];
        if ($refund_value == 20) {

            //买家寄回物流信息
            $express = (new \app\common\models\order\Express())->getExpress($order->hasOneRefundApply->returnExpress->express_code, $order->hasOneRefundApply->returnExpress->express_sn);

            $dispatch['express_sn'] = $order->hasOneRefundApply->returnExpress->express_sn;
            $dispatch['company_name'] = $order->hasOneRefundApply->returnExpress->express_company_name;
            $dispatch['data'] = $express['data'];
            $dispatch['thumb'] = $order->hasManyOrderGoods[0]->thumb;
            $dispatch['tel'] = '95533';
            $dispatch['status_name'] = $express['status_name'];
        } elseif ($refund_value == 30) {
            //商家发货物流信息
            $express = (new \app\common\models\order\Express())->getExpress($order->hasOneRefundApply->resendExpress->express_code, $order->hasOneRefundApply->resendExpress->express_sn);
            $dispatch['express_sn'] = $order->hasOneRefundApply->resendExpress->express_sn;
            $dispatch['company_name'] = $order->hasOneRefundApply->resendExpress->express_company_name;
            $dispatch['data'] = $express['data'];
            $dispatch['thumb'] = $order->hasManyOrderGoods[0]->thumb;
            $dispatch['tel'] = '95533';
            $dispatch['status_name'] = $express['status_name'];
        }


        return $this->successJson('查询成功',$dispatch);
    }

}