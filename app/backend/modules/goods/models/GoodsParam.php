<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/22
 * Time: 下午18:16
 */
namespace app\backend\modules\goods\models;


class GoodsParam extends \app\common\models\GoodsParam
{
    static protected $needLog = true;


    public static function store($goods_id, $parameter)
    {
        $paramids = [];
        foreach ($parameter as  $key => $item) {
            $param = [
                "uniacid" => \YunShop::app()->uniacid,
                "title" => $item['title'],
                "value" => $item['value'],
                "displayorder" => $key,
                "goods_id" => $goods_id
            ];
            if (!intval($item['id'])) {
                $goods_param = self::create($param);
                $param_id = $goods_param->id;
            } else {
                $goods_param = GoodsParam::updateOrCreate(['id' => intval($item['id'])], $param);
                $param_id = $goods_param->id;
            }
            $paramids[] = $param_id;
        }

        //删除本商品其它规格
        if (count($paramids) > 0) {
            GoodsParam::where('goods_id', '=', $goods_id)->whereNotIn('id', $paramids )->delete();

        } else {
            GoodsParam::where('goods_id', '=', $goods_id)->delete();
        }
    }

    public static function saveParam($data, $goods_id)
    {
        $param_ids = $data->param_id;
        $param_titles = $data->param_title;
        $param_values = $data->param_value;
        $param_displayorders = $data->param_displayorder;
        $paramLen = count($param_ids);
        $paramids = [];
        for ($paramIndex = 0; $paramIndex < $paramLen; $paramIndex++) {
            $param_id = "";
            $get_param_id = $param_ids[$paramIndex];
            $param = [
                "uniacid" => \YunShop::app()->uniacid,
                "title" => $param_titles[$paramIndex],
                "value" => $param_values[$paramIndex],
                "displayorder" => $paramIndex,
                "goods_id" => $goods_id
            ];

            if (!is_numeric($get_param_id)) {
                $goods_param = GoodsParam::Create($param);
                $param_id = $goods_param->id;
            } else {
                GoodsParam::updateOrCreate(['id' => $get_param_id], $param);
                $param_id = $get_param_id;
            }
            $paramids[] = $param_id;
        }

        //删除本商品其它规格
        if (count($paramids) > 0) {
            GoodsParam::where('goods_id', '=', $goods_id)->whereNotIn('id', $paramids )->delete();

        } else {
            GoodsParam::where('goods_id', '=', $goods_id)->delete();
            //pdo_query('delete from ' . tablename('sz_yi_goods_param') . " where goodsid=$id");
        }
    }
}