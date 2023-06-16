<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/4/26
 * Time: 11:38
 */

namespace app\frontend\modules\cart\services;


use app\common\models\Goods;

interface CartGoodsInterface
{
    /**
     * @return bool
     */
    public function verify();


    //商品数据加载之前
    public function beforeCreating();

    /**
     * 商品单位
     * @return string
     */
    public function getUnit();

    /**
     * 商品样式
     * @return mixed
     */
    public function getStyleType();
}