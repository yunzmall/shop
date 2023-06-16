<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/28
 * Time: 16:04
 */

namespace app\backend\modules\goods\services;


use app\backend\modules\goods\models\GoodsOption;
use app\backend\modules\goods\models\GoodsSpec;
use app\backend\modules\goods\models\GoodsSpecItem;

class SpecOptionService
{

    protected static $instance = null;


    protected $parameter;

    protected $goods_id;

    protected $uniacid;

    public function __construct()
    {

    }

    public static function store($goods_id,$option, $uniacid)
    {
        $self = self::getInstance();

        $self->parameter = $option;

        $self->goods_id = $goods_id;

        $self->uniacid = $uniacid;


//        if (empty($option['specs']) || empty($option['option'])) {
//            return;
//        }

        if (!$option['has_option'] && empty($option['specs'])) {
            GoodsSpec::where('goods_id', '=', $goods_id)->delete();
            GoodsOption::where('goods_id', '=', $goods_id)->delete();
            return;
        }


        //规格项
        $spec_items = $self->saveSpec();

        //规格信息
        $self->saveOption($spec_items);
    }

    public function saveSpec()
    {
        $specs = $this->parameter['specs'];

        $spec_items = [];
        foreach ($specs as $specIndex => $spec) {
            $spec_data = [
                "uniacid" => $this->uniacid,
                "goods_id" => $this->goods_id,
                "display_order" => $specIndex,
                "title" => $spec['title']
            ];
            //新添加规格
            $tag = substr($spec['id'], 0, 2);
            if ('SC' == strtoupper($tag)) {
                $goods_spec = GoodsSpec::Create($spec_data);
                $spec_id = $goods_spec->id;
            } else {
                $goods_spec = GoodsSpec::updateOrCreate(['id' => $spec['id']], $spec_data);
                $spec_id = $goods_spec->id;
            }

            foreach ($spec['spec_item'] as $valueIndex => $value) {
                $specItem = [
                    "uniacid" => $this->uniacid,
                    "specid" => $spec_id,
                    "display_order" => $valueIndex,
                    "title" => $value['title'],
                    "show" =>  $value['show']?:1,
                    "thumb" =>'',
                    "virtual" => '',
                ];

                //新添加规格
                $valueTag = substr($value['id'], 0, 2);
                if ('SV' == strtoupper($valueTag)) {
                    $goods_spec_item = GoodsSpecItem::Create($specItem);
                    $item_id = $goods_spec_item->id;
                } else {
                    $goods_spec_item = GoodsSpecItem::updateOrCreate(['id' => $value['id']], $specItem);
                    $item_id = $goods_spec_item->id;
                }

                $itemids[] = $item_id;
                $spec_items[$value['id']] = $item_id;
            }
            if (count($itemids) > 0) {
                GoodsSpecItem::where('specid', '=', $spec_id)->whereNotIn('id', $itemids)->delete();
            } else {
                GoodsSpecItem::where('specid', '=', $spec_id)->delete();
            }

            GoodsSpec::updateOrCreate(['id' => $spec_id], ['content' => serialize($itemids)]);
            $specids[] = $spec_id;
        }

        if (count($specids) > 0) {
            GoodsSpec::where('goods_id', '=', $this->goods_id)->whereNotIn('id', $specids)->delete();
        } else {
            GoodsSpec::where('goods_id', '=', $this->goods_id)->delete();
        }
        return $spec_items;
    }

    public function saveOption($spec_items)
    {
        $optionPost =   $specs = $this->parameter['option'];
        foreach ($optionPost as $oKey =>  $option) {
            //规格项id处理
            $spec_item_ids = explode('_',$option['specs']);
            $specs_id_array = array_map(function ($spec_item_id) use ($spec_items) {
                return $spec_items[$spec_item_id];
            },$spec_item_ids);

            $goodsOption = [
                "uniacid" => $this->uniacid,
                "goods_id" => $this->goods_id,
                "title" =>$option['title'],
                "product_price" => floatVal($option['product_price']),
                "cost_price" =>  floatVal($option['cost_price']),
                "market_price" => floatVal($option['market_price']),
                "stock" => $option['stock']?:0,
                "weight" => floatVal($option['weight']),
                "volume" => floatVal($option['volume']),
                "goods_sn" => $option['goods_sn'],
                "product_sn" => $option['product_sn'],
                'thumb' => $option['thumb'],
                "specs" => implode('_',$specs_id_array),
                'virtual' => 0,
                "red_price" => '',
                "display_order" => $oKey,
            ];

            $optionTag = substr($option['id'], 0, 2);
            if ('SP' == strtoupper($optionTag)) {
                $goodsOptionModel = GoodsOption::create($goodsOption);
                $option_id = $goodsOptionModel->id;
            } else {
                $goodsOptionModel = GoodsOption::updateOrCreate(['id' => $option['id']], $goodsOption);
                $option_id = $goodsOptionModel->id;
            }

            $optionids[] = $option_id;
        }

        if (count($optionids) > 0) {
            GoodsOption::where('goods_id', '=', $this->goods_id)->whereNotIn('id', $optionids)->delete();
        } else {
            GoodsOption::where('goods_id', '=', $this->goods_id)->delete();
        }

   }


    /**
     * 单例缓存
     * @return null|self
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance =  new self();

        }
        return self::$instance;

    }
}