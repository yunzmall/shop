<?php
/**
 * Created by PhpStorm.
 * User: king/QQ:995265288
 * Date: 2018/12/30
 * Time: 5:02 PM
 */

namespace app\common\observers\income;


use app\common\events\income\IncomeCreatedEvent;
use app\common\models\Income;
use app\common\models\income\IncomeLog;
use app\common\observers\BaseObserver;
use app\common\services\plugin\DeliveryDriverSet;
use Illuminate\Database\Eloquent\Model;

class IncomeObserver extends BaseObserver
{
    public function created(Model $model)
    {
        event(new IncomeCreatedEvent($model));
    }

    public function saved(Model $model)
    {
        $income_log_data = [
            'uniacid'       => \YunShop::app()->uniacid,
            'income_id'     => $model->id,
            'before'        => collect($model->getDirty())->map(function($value,$key) use ($model){
                return $model->getOriginal($key);
            }),
            'after'         => json_encode($model->getDirty()),
            'remark'        =>  ''
        ];
        IncomeLog::create($income_log_data);
    }

    public function updated(Model $model)
    {
        $income_log_data = [
            'uniacid'       => \YunShop::app()->uniacid,
            'income_id'     => $model->id,
            'before'        => collect($model->getDirty())->map(function($value,$key) use ($model){
                return $model->getOriginal($key);
            }),
            'after'         => json_encode($model->getDirty()),
            'remark'        =>  ''
        ];
        IncomeLog::create($income_log_data);
    }


}
