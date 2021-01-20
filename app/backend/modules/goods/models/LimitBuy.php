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
//        dd($data);
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
        $data['goods_id'] = $goodsId;
        $data['uniacid'] = \YunShop::app()->uniacid;
        $data['status'] = empty($data['status']) ? 0 : $data['status'];
        $data['start_time'] = strtotime($data['time']['start']);
        $data['end_time'] = strtotime($data['time']['end']);

        unset($data['time']);

        $changeTime = 0;
        if($saleModel->end_time <> $data['end_time'] && $data['end_time'] >= time() && $data['status']){
            $changeTime = 1;
        }

        $saleModel->setRawAttributes($data);

//        dd($saleModel);
        $res = $saleModel->save();
        if($res && $changeTime){
            //触发限时购延时队列任务（用于后续触发限时购商品下架事件）
            $diff = ($data['end_time'] - time()) + 30;
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

    public static function relationValidator($goodsId, $data, $operate)
    {
//        dd($data);
        $flag = false;
        $model = new static;
        $validator = $model->validator($data);
        if($validator->fails()){
            $model->error($validator->messages());
        }else{
            $flag = true;
        }
        return $flag;
    }

}