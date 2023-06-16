<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/10
 * Time: 14:09
 */

namespace app\backend\modules\goods\widget;


use app\backend\modules\goods\models\GoodsOption;
use app\common\models\goods\GoodsSpecInfo;

class SpecInfoWidget extends BaseGoodsWidget
{
    public $group = 'base';

    public $widget_key = 'spec_info';

    public $code = 'spec_info';

    public function pluginFileName()
    {
        return 'spec_info';
    }

    /**
     * 权限判断
     * @return boolean
     */
    public function usable()
    {
        if (in_array($this->goods->plugin_id,[0,92])) {
            return true;
        }
        return false;
    }

    public function getData()
    {
        $specs_info = [];
        $options = [];
        if (!is_null($this->goods)) {
            if ($this->goods->has_option) {
                $options = GoodsOption::uniacid()
                    ->select('id','title')
                    ->where('goods_id',$this->goods->id)
                    ->get()->toArray();
            }
            $specs_info = GoodsSpecInfo::uniacid()
                ->where('goods_id',$this->goods->id)
                ->get()->toArray();
        }

        return [
            'options'=> $options,
            'specs_info'=> $specs_info,
        ];
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}