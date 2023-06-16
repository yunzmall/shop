<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/21
 * Time: 15:44
 */

namespace app\common\modules\goods;

use app\common\modules\goods\queue\GoodsUpdateObserverQueue;
use app\common\observers\BaseObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * 不要往这个观察者类加任何业务程序
 * Class GoodsObserverBase
 * @package app\common\modules\goods
 */
class GoodsOptionObserverBase extends BaseObserver
{
    public function saving(Model $model)
    {
    }


    public function saved(Model $model)
    {


        //有修改退库存；这库存变动不能放到队列里，原因是再队列里无法区分是否变动
        if ($model->isDirty('stock')) {
            try {
                \Log::debug('<----商品规格编辑库存变动----'.$model->id);
                $requestData = request()->input();
                event(new \app\common\modules\goods\events\GoodsOptionStockChangeEvent($model,$requestData));
            } catch (\Exception $exception) {
                \Log::debug("----{$model->id}--规格库存变动-error---". $exception->getMessage(),[$exception->getFile(),$exception->getLine()]);
            }

        }


    }

    public function updating(Model $model)
    {
    }

    public function updated(Model $model)
    {

    }
}