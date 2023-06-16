<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/1/13
 * Time: 16:45
 */

namespace app\frontend\modules\order\operations\member;

use app\common\models\comment\CommentConfig;
use app\frontend\modules\order\operations\OrderOperation;

class CommentGoods extends OrderOperation
{
    public function getApi()
    {
        return '';
    }

    public function getValue()
    {
        return static::GOODS_COMMENT;
    }

    public function getName()
    {
        return '评价';
    }

    public function enable()
    {
        $is_order_comment_entrance = CommentConfig::getSetConfig('is_order_comment_entrance');
        //未开启设置
        if (!$is_order_comment_entrance) {
            return false;
        }

        

        return true;
//        return $this->order->canRefund();
    }

}