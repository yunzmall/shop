<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/5
 * Time: 16:39
 */

namespace app\outside\modules\order\controllers;


use app\common\helpers\Url;
use app\frontend\models\Goods;
use app\outside\controllers\OutsideController;
use app\common\exceptions\ApiException;
use app\frontend\models\Member;
use app\frontend\models\GoodsOption;
use app\common\modules\trade\models\Trade;
use app\frontend\modules\member\services\MemberCartService;
use app\frontend\modules\memberCart\MemberCartCollection;
use app\outside\modes\OutsideOrder;
use app\outside\modules\order\models\BuyTrade;

class BuyController extends OutsideController
{
    public function preAction()
    {

        app('GoodsManager')->bind('Goods', function ($goodsManager, $attributes) {
            return new Goods($attributes);
        });

        app('GoodsManager')->bind('GoodsOption', function ($goodsManager, $attributes) {
            return new GoodsOption($attributes);
        });

        parent::preAction();
    }

    /**
     * @throws \app\common\exceptions\AppException
     */
    protected function validateParam()
    {
        $this->validate([
            'goods' => 'required',
            'uid' => 'required',
        ],null, [
            'goods.required' => 'goods 参数必须填写',
            'uid.required' =>  '购买会员标识必须填写',
        ]);
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


        return $this->successJson('成功', $trade);
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


    /**
     * @throws ApiException
     */
    public function setMember()
    {
        $member = Member::where('uid', request()->input('uid'))->first();

        if (!$member) {
            throw new ApiException('会员不存在');
        }


        Member::$current = $member;
    }

}