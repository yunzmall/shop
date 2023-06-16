<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 下午3:55
 */

namespace app\frontend\modules\order\discount;

use app\common\facades\Setting;
use app\common\modules\orderGoods\models\PreOrderGoods;

class FullPiece extends BaseDiscount
{
    protected $code = 'fullPiece';
    protected $name = '满件优惠';

    /**
     * 获取总金额
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {
        $settings = Setting::get('shop.fullPieceNew');
        if(!$settings['open']){
            return 0;
        }
        //只有商城订单参加 益生插件订单
        if(!in_array($this->order->plugin_id,[0,61])){
            return 0;
        }
        if (empty($settings['fullPiece'])) {
            return 0;
        }

        $fullPieces = [];
        foreach ($settings['fullPiece'] as $k=>$v) {
            $fullPieces[$k] = $v;
            $fullPieces[$k]['goods'] = [];
            if (empty($v['goods'])) {
                continue;
            }
            foreach ($this->order->orderGoods as $orderGoods) {
                if (in_array($orderGoods->goods_id,$v['goods'])) {
                    $fullPieces[$k]['goods'][] = $orderGoods->goods_id;
                }
            }
        }
        $result = $this->totalAmount($fullPieces);
        return $result;
    }

    /**
     * @param $fullPieces
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    private function totalAmount($fullPieces)
    {
        // 求和所属订单中指定goods_id的订单商品支付金额
        $discount = 0;
        foreach ($fullPieces as $fullPiece) {
            if (empty($fullPiece['goods'])) {
                continue;
            }
            $goods_count = $this->order->orderGoods->whereIn('goods_id', $fullPiece['goods'])->sum('total');

            $rules = collect($fullPiece['rules']);
            $rules = $rules->sortByDesc(function ($rule) {
                return $rule['enough'];
            });
            $amount = $this->order->orderGoods->whereIn('goods_id', $fullPiece['goods'])->sum(function (PreOrderGoods $preOrderGoods) {
                return $preOrderGoods->getPriceBefore($this->getCode());
            });
            foreach ($rules as $rule) {
                if ($goods_count < $rule['enough']) {
                    continue;
                }
                if ($fullPiece['discount_type']) {//折扣
                    $reduce = bcsub(10,$rule['reduce'],2);
                    $discount += bcmul(bcdiv($reduce,10,2),$amount,2);
                    break;
                } else {//立减
                    $discount += $rule['reduce'];
                    break;
                }
            }
        }
        return min($discount,$this->order->getPriceBefore($this->getCode()));
    }
}