<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/1/13
 * Time: 15:03
 */

namespace app\outside\modules\order\controllers;


use app\common\helpers\Url;
use app\frontend\modules\order\models\PreOrder;
use app\outside\controllers\OutsideController;
use app\frontend\models\Goods;
use app\common\exceptions\ApiException;
use app\frontend\models\Member;
use app\frontend\models\GoodsOption;
use app\common\modules\trade\models\Trade;
use app\frontend\modules\member\services\MemberCartService;
use app\frontend\modules\memberCart\MemberCartCollection;
use app\outside\modes\OutsideOrder;
use app\outside\modules\order\models\BuyTrade;

class CreateController extends OutsideController
{
    public function preAction()
    {
        app()->bind(Trade::class, function () {
            return new BuyTrade();
        });

        app('GoodsManager')->bind('Goods', function ($goodsManager, $attributes) {
            return new Goods($attributes);
        });

        app('GoodsManager')->bind('GoodsOption', function ($goodsManager, $attributes) {
            return new GoodsOption($attributes);
        });

        parent::preAction();
    }

    protected function validateParam()
    {
        $this->validate([
            'outside_sn' => 'required',
            'uid' => 'required',
            'goods' => 'required',
        ],null, [
            'outside_sn.required' => 'outside_sn 参数必须填写',
            'uid.required' =>  '购买会员标识必须填写',
            'goods.required' =>  'goods 参数必须填写',
        ]);

        $isExist = OutsideOrder::where('outside_sn',request()->input('outside_sn'))->first();
        if($isExist) {
            throw new ApiException('订单号已存在无法重复下单');
        }

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     * @throws \app\common\exceptions\AppException
     */
    public function index()
    {
        $this->validateParam();

        $this->setMember();

        $trade = $this->getMemberCarts()->getTrade(Member::current(), $this->requestParam());
        //订单创建保存
        $trade->generate();

        $data = [
            'outside_sn' => $trade->outsideOrder->outside_sn,
            'total_price' => sprintf('%.2f',$trade->outsideOrder->total_price),
            'trade_sn' => $trade->outsideOrder->trade_sn,
            'pay_link' => Url::absoluteApp('member/orderpay/'.$trade->orders->pluck('id')->implode(',')),
            'orders' => $trade->orders->map(function (PreOrder $order) {
                return [
                    'order_id' => $order->id,
                    'order_sn' => $order->order_sn,
                    'price' => sprintf('%.2f', $order->price),
                ];
            }),
        ];
        return $this->successJson('成功', $data);
    }

    protected function requestParam()
    {
        return request();
    }

    protected function getMemberCarts()
    {
        $goods_params = request()->input('goods');

        if (empty($goods_params)) {
            throw new ApiException('无法获取到下单商品');
        }

        $result = collect($goods_params)->map(function ($memberCart) {
            return MemberCartService::newMemberCart($memberCart);
        });
        $memberCarts = new MemberCartCollection($result);
        $memberCarts->loadRelations();
        return $memberCarts;
    }


    public function setMember()
    {
        $member = Member::where('uid', request()->input('uid'))->first();

        if (!$member) {
            throw new ApiException('会员不存在');
        }


        Member::$current = $member;
    }
}