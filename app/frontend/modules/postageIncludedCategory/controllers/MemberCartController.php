<?php
/**
 * Author:
 * Date: 2017/8/1
 * Time: 下午5:19
 */

namespace app\frontend\modules\postageIncludedCategory\controllers;

use app\framework\Http\Request;
use app\frontend\modules\member\controllers\MemberCartController as BaseCartController;
use app\frontend\modules\postageIncludedCategory\models\CategoryMemberCart;
use app\common\models\VarietyMemberCart;
use app\common\facades\Setting;

/**
 * 包邮分类会员购物车
 */
class MemberCartController extends BaseCartController
{
    const PLUGIN_IDS = [0, 92, 44];

    // 平台自营商品，供应商和供应链
    public function index(Request $request, $integrated = null)
    {
        $content = parent::index($request, $integrated);
        $memberCart = $content->getData();

        // 得到会员购物车数据并且满额包邮开启时
        if ($memberCart->result && Setting::get('enoughReduce.freeFreight.open')) {
            $data = [];
            foreach ($memberCart->data as $cart) {
                $categoryMemberCart = CategoryMemberCart::find($cart->id);
                if ($categoryMemberCart->varietiy()) {
                    $data[] = $cart;
                }
            }
            $memberCart->data = $data;
            $content->setData($memberCart);

        } else {
            $memberCart->result = 0;
            $memberCart->msg = '请确认订单满额包邮是否开启';
            $memberCart->data = [];
            $content->setData($memberCart);
        }

        return $content;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        // 如果捕获到商品有限制条件. 那么直接找到是否存在在购物车, 存在的话. 直接关联包邮分类的数据即可.
        try {
            $content = parent::store();
        } catch (\Exception $e) {
            $data = array(
                'member_id' => \YunShop::app()->getMemberId(),
                'uniacid' => \YunShop::app()->uniacid,
                'goods_id' => request()->input('goods_id'),
                'total' => request()->input('total'),
                'option_id' => (int)request()->input('option_id', 0),
            );

            $hasGoodsModel = app('OrderManager')->make('MemberCart')->hasGoodsToMemberCart($data);
            $cart_id = $hasGoodsModel['id'];
            VarietyMemberCart::firstOrCreate([
                'uniacid' => \YunShop::app()->uniacid,
                'member_cart_id' => $cart_id,
                'member_cart_type' => 'PostageIncludedCategory',
            ]);

            return $this->successJson('ok', [
                'cart_id' => $cart_id,
                'cart_num' => \app\frontend\models\MemberCart::getCartNum(\YunShop::app()->getMemberId()),
            ]);
        }

        // 在包邮分类页面添加的商品都应该为购物车添加一条关联数据: 包邮分类购物车表
        if ($content->getData()->result) {
            $cart_id = $content->getData()->data->cart_id;
            $memberCart = CategoryMemberCart::find($cart_id);

            // 查不到关联数据, 说明用户第一次添加购物车该商品. 创建新的关联关系.
            if (in_array($memberCart->plugin_id, self::PLUGIN_IDS) && !$memberCart->varietiy()) {
                // 添加包邮分类关联
                VarietyMemberCart::create([
                    'uniacid' => \YunShop::app()->uniacid,
                    'member_cart_id' => $cart_id,
                    'member_cart_type' => 'PostageIncludedCategory',
                ]);
            }
        }
        return $content;
    }
}