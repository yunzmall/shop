<?php


namespace app\platform\modules\pluginsSetMeal\models;

use app\common\models\BaseModel;

class PluginsMealModel extends BaseModel
{
    protected $table = 'yz_plugins_meal';
    protected $fillable = ['order_by','name','plugins','state'];


    // 获取插件套餐数据，并返回套餐插件的数量
    public function getPluginsMealList($id = null)
    {
        $list = self::select('id', 'name', 'plugins', 'order_by','state');

        if ($id){
            $list = $list->where('id',$id);
        }

        $list = $list->orderBy('order_by', 'DESC')
            ->get()
            ->toArray();
        foreach ($list as &$item) {
            $item['plugins'] = explode(',', $item['plugins']);
            $item['count'] = count($item['plugins']);
        }
        return $list;
    }
}