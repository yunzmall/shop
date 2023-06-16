<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 24/02/2017
 * Time: 01:01
 */

namespace app\common\observers;


use Illuminate\Database\Eloquent\Model;

class BaseObserver {

    public function saving(Model $model) {}

    public function saved(Model $model) {}

    public function updating(Model $model) {}

    public function updated(Model $model) {}

    public function creating(Model $model) {}

    public function created(Model $model) {}

    public function deleting(Model $model) {}

    public function deleted(Model $model) {}

    public function restoring(Model $model) {}

    public function restored(Model $model) {}

    /**
     * 插件观察
     * @param $key
     * @param $model
     * @param string $operate
     * @return array
     */
    protected function pluginObserver($key, $model, $operate = 'created', $type = null)
    {
        $observerConfigs = \app\common\modules\shop\ShopConfig::current()->get($key);
        //按照权重从小到大排序执行
        $observerConfigs = collect($observerConfigs)->sortBy('weight')->toArray();
        $result = [];
        if($observerConfigs){
            foreach ($observerConfigs as $pluginName=>$pluginOperators){
                if(isset($pluginOperators) && $pluginOperators) {
                    $class = array_get($pluginOperators,'class');
                    $function =array_get($pluginOperators,$operate == 'validator' ? 'function_validator':'function_save');
                    $data = array_get($model->widgets,$pluginName,[]);
                    if(class_exists($class) && method_exists($class,$function) && is_callable([$class,$function])){
                        if (!$type) {
                            $result[$pluginName] = $class::$function($model->id, $data, $operate);
                        } else {
                            $result[$pluginName] = $class::$function($model);
                        }
                    }
                }
            }
        }
        return $result;
    }
}