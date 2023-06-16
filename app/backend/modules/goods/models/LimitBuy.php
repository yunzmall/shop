<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/20 0020
 * Time: 下午 3:46
 */

namespace app\backend\modules\goods\models;

use app\common\traits\MessageTrait;
use app\common\models\goods\GoodsLimitBuy;
use app\Jobs\LimitBuyEndJob;
use Carbon\Carbon;

class LimitBuy extends GoodsLimitBuy
{
    use MessageTrait;

    public static function relationSave($goodsId, $data, $operate)
    {
        if (!$goodsId) {
            return false;
        }
        if (!$data) {
            return false;
        }
        $saleModel = self::getModel($goodsId, $operate);
        if ($operate == 'deleted') {
            return $saleModel->delete();
        }
        $saveData['goods_id'] = $goodsId;
        $saveData['uniacid'] = \YunShop::app()->uniacid;
        $saveData['status'] = empty($data['status']) ? 0 : $data['status'];
        $saveData['start_time'] = $data['start_time'];
        $saveData['end_time'] = $data['end_time'];
        $saveData['display_name'] = $data['display_name'];
        $changeTime = 0;
        if($saleModel->end_time <> $saveData['end_time'] && $saveData['end_time'] >= time() && $saveData['status']){
            $changeTime = 1;
        }
        $saleModel->setRawAttributes($saveData);
        if ($saleModel->getOriginal('start_time') != $saveData['start_time'] || $saleModel->getOriginal('end_time') != $saveData['end_time']) {
            $goods = self::getGoodsById($goodsId);
            $saleModel->original_stock = $goods->stock;
        }
        $res = $saleModel->save();
        if($res && $changeTime){
            //触发限时购延时队列任务（用于后续触发限时购商品下架事件）
            $diff = ($saveData['end_time'] - time()) + 30;
            $job = (new LimitBuyEndJob($saleModel->toArray()))->delay(Carbon::now()->addSeconds(intval($diff)));
            dispatch($job);
        }
        return $res;
    }

    public static function getModel($goodsId, $operate)
    {
        $model = false;
        if ($operate != 'created') {
            $model = static::where(['goods_id' => $goodsId])->first();
        }
        !$model && $model = new static;

        return $model;
    }

    public function getGoodsById($goodsId)
    {
        return Goods::find($goodsId);
    }
}