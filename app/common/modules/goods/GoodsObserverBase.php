<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/11/18
 * Time: 16:35
 */

namespace app\common\modules\goods;

use app\common\events\goods\GoodsDestroyEvent;
use app\common\modules\goods\queue\GoodsUpdateObserverQueue;
use app\common\observers\BaseObserver;
use app\Jobs\GoodsSetPriceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * 不要往这个观察者类加任何业务程序
 * Class GoodsObserverBase
 * @package app\common\modules\goods
 */
class GoodsObserverBase extends BaseObserver
{
    public function saving(Model $model)
    {
        //先给个默认值
        $model->min_price = $model->price;
        $model->max_price = $model->price;
    }


    public function saved(Model $model)
    {

        if ($model->has_option) {
            dispatch(new GoodsSetPriceJob($model->uniacid,$model->id))->delay(now()->addMinutes(1));//有规格的走延时队列更新最大/最小价格
        }

        //有修改退库存；这库存变动不能放到队列里，原因是再队列里无法区分是否变动
        if ($model->isDirty('stock')) {
            try {
                \Log::debug('<----商品编辑库存变动----'.$model->id);
                $requestData = request()->input();
                event(new \app\common\modules\goods\events\GoodsStockChangeEvent($model,$requestData));
            } catch (\Exception $exception) {
                \Log::debug("----{$model->id}--库存变动-error---". $exception->getMessage(),[$exception->getFile(),$exception->getLine()]);
            }

        }

        if ($model->isDirty('status')) {
            try {
                \Log::debug('<----商品编辑上下架变动----'.$model->id);
                $requestData = request()->input();
                event(new \app\common\modules\goods\events\GoodsOffshellChangeEvent($model,$requestData));
            } catch (\Exception $exception) {
                \Log::debug("----{$model->id}--上下架变动-error---". $exception->getMessage(),[$exception->getFile(),$exception->getLine()]);
            }

        }

    }

    public function updating(Model $model)
    {
        (new \app\common\services\operation\GoodsLog($model, 'update'));
        //$event = event(new \app\common\modules\goods\events\GoodsStockChangeEvent($model,request()->input()));
    }

    public function updated(Model $model)
    {
        //$event = event(new \app\common\modules\goods\events\GoodsStockChangeEvent($model,request()->input()));

    }

    public function deleted(Model $model)
    {
        event(new GoodsDestroyEvent($model->id));
    }
}