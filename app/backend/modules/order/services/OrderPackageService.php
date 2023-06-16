<?php

namespace app\backend\modules\order\services;

use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\models\order\OrderPackage;
use app\frontend\models\OrderGoods;
use Illuminate\Support\Collection;

class OrderPackageService
{
    /**
     * 过滤商品数量
     * @param Collection $orderGoodsCollect
     * @param Collection $orderPackageCollect
     * @return Collection
     */
    public static function filterGoods(Collection $orderGoodsCollect, Collection $orderPackageCollect)
    {
        if ($orderGoodsCollect->isEmpty()) {
            return $orderGoodsCollect;
        }

        if ($orderPackageCollect->isEmpty()) {
            return $orderGoodsCollect;
        }

        return $orderGoodsCollect->filter(function (&$item, $key) use ($orderPackageCollect) {
            $item['total'] -= $orderPackageCollect->where('order_id', $item['order_id'])
                ->where('order_goods_id', $item['id'])
                ->sum('total');
            if ($item['total'] <= 0) {
                return false;
            }
            return true;
        });
    }

    /**
     * 校验单包裹商品
     * @param Collection $orderPackageCollect
     * @param Collection $orderGoodsCollect
     * @return bool
     * @throws AppException
     */
    public static function checkGoodsPackage(Collection $orderPackageCollect, Collection $orderGoodsCollect)
    {
        $orderPackageCollect->each(function (&$item, $key) use ($orderPackageCollect, $orderGoodsCollect) {
            $goods = $orderGoodsCollect->where('id', $item['order_goods_id']);
            if ($goods->isEmpty()) {
                throw new ShopException('商品id:' . $item['order_goods_id'] . '不存在或已全部发货');
            }
            $num = $goods->sum('total') - $orderPackageCollect->where('order_goods_id', $item['order_goods_id'])->sum('total');
            if ($num < 0) {
                throw new ShopException('商品id:' . $item['order_goods_id'] . '超发数量' . abs($num));
            }
        });

        return true;
    }

    /**
     * 单包裹发货(一个物流)
     * @param int $order_id
     * @param int $express_id
     * @param Collection $orderGoodsCollect
     * @return \app\common\models\BaseModel|false
     */
    public static function saveOneOrderPackage(int $order_id, int $express_id, $orderGoods)
    {
        if (!$order_id or !$express_id) {
            return false;
        }
        $data = array();
        foreach ($orderGoods as $v) {
            $data[] = [
                'uniacid' => \YunShop::app()->uniacid,
                'order_id' => $order_id,
                'order_goods_id' => $v['order_goods_id'] ?: $v['id'],
                'total' => $v['total'],
                'order_express_id' => $express_id,
                'created_at' => time()
            ];
        }
        
        return OrderPackage::insert($data);
    }

    /**
     * 获取剩余未发货的商品
     * @param $order_id
     * @return Collection
     */
    public static function getNotDeliverGoods($order_id)
    {
        $order_goods = OrderGoods::uniacid()->where('order_id',$order_id)->whereNull('order_express_id')->get()->makeVisible('order_id');
        $order_package = OrderPackage::getOrderPackage($order_id)->where('order_express_id','!=',false);
        return static::filterGoods($order_goods,$order_package);
    }
}