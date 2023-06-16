<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/15
 * Time: 16:32
 */

namespace app\backend\modules\goods\widget;

use app\common\models\goods\GoodsFiltering;
use app\common\models\SearchFiltering;

//商品标签
class FilteringWidget extends BaseGoodsWidget
{
    public $group = 'tool';

    public $widget_key = 'filtering';

    public $code = 'tags';

    public function pluginFileName()
    {
        return 'goods';
    }

    public function getData()
    {
        $filtering = $this->getFilteringList();

        $goods_filter = GoodsFiltering::select('filtering_id')->ofGoodsId($this->goods->id)->get()->toArray();
        $goods_filter = array_pluck($goods_filter, 'filtering_id');

        return [
            'filtering' => $filtering,
            'goods_filter' => $goods_filter,
        ];
    }

    public function getFilteringList()
    {
        $filtering = SearchFiltering::where('parent_id', 0)->where('is_show', 0)->get();

        foreach ($filtering as $key => &$value) {
            $value['value'] = SearchFiltering::select('id', 'parent_id', 'name')->where('parent_id', $value->id)->get()->toArray();
        }
        return $filtering->toArray();
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}