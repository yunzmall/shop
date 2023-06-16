<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/28
 * Time: 13:48
 */
namespace app\backend\modules\goods\models;

use app\common\helpers\Cache;

class GoodsSetting extends \app\common\models\GoodsSetting
{
    public static function saveSet($data)
    {
		Cache::forget("public_setting");
        return static::Create(self::getData($data));
    }

    public static function getData($data)
    {
        return [
            'uniacid' => \YunShop::app()->uniacid,
            'is_month_sales' => $data['is_month_sales'],
            'is_member_enter' => $data['is_member_enter'],
            'is_price_desc' => $data['is_price_desc'],
            'title' => empty($data['title']) ? '' : $data['title'],
            'explain' => empty($data['explain']) ? '' : $data['explain'],
            'detail_show'	=> $data['detail_show'] ?: 0,
            'scribing_show' => $data['scribing_show'] ? : 0,
        ];
    }
}