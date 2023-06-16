<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/9/14
 * Time: 17:38
 */

namespace app\backend\modules\goods\widget;


//商品规格
use app\backend\modules\goods\models\GoodsOption;
use app\backend\modules\goods\models\GoodsSpec;

class OptionWidget extends BaseGoodsWidget
{
    public $group = 'base';

    public $widget_key = 'option';

    public $code = 'option';

    public function pluginFileName()
    {
        return 'goods';
    }

    public function getData()
    {
        $specs = [];
        $option = [];
        if (!is_null($this->goods)) {
            $goodsSpecs = GoodsSpec::select('id','title','goods_id')->where('goods_id', $this->goods->id)
                ->with(['hasManySpecsItem' => function($item) {
                    return $item->select('id','specid','title','show')->orderBy('display_order', 'asc');
                }])->orderBy('display_order', 'asc')->get();

            $spec_title_key = [];
            $spec_item_title_arr = [];
            if (!$goodsSpecs->isEmpty()) {
                foreach ($goodsSpecs as $spec) {
                    $temporary = $spec->getAttributes();
                    $temporary['spec_item'] = $spec->hasManySpecsItem->toArray();

                    $specs[] = $temporary;
                    $spec_item_title_arr = $spec_item_title_arr + array_column($temporary['spec_item'],'title','id');
                    $spec_title_key[$temporary['title']] = array_column($temporary['spec_item'],'id');
                }
            }
            $option = GoodsOption::where('goods_id', $this->goods->id)->orderBy('display_order', 'asc')->get()->toArray();

            foreach ($option as $key=>$item) {

                if ($item['thumb']) {
                    $option[$key]['thumb'] = yz_tomedia($item['thumb']);
                }
                //这里那id做判断，名称可能会重复
                $spec_item_ids = explode('_',$item['specs']);

                foreach ($spec_item_ids as $title_key => $spec_item_id) {
                    $spec_title_array = array_filter($spec_title_key, function ($title_key) use ($spec_item_id) {
                        return in_array($spec_item_id, $title_key);
                    });
                    $title_key_first = array_key_first($spec_title_array);
                    if ($title_key_first) {
                        $option[$key][$title_key_first] = $spec_item_title_arr[$spec_item_id];
                    }
                }
            }

        }

        return [
            'has_option'=> is_null($this->goods)?0:$this->goods->has_option,
            'specs'=> $specs,
            'option'=>$option
        ];
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}