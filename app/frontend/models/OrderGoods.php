<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/21
 * Time: 下午1:58
 */

namespace app\frontend\models;

use app\common\models\comment\CommentConfig;
use app\frontend\models\goods\Sale;
use app\frontend\modules\goods\models\Comment;

/**
 * Class OrderGoods
 * @package app\frontend\models
 * @property GoodsOption goodsOption
 * @property Goods belongsToGood
 */
class OrderGoods extends \app\common\models\OrderGoods
{

    public function scopeDetail($query)
    {
        return $query->select(['id', 'order_id', 'refund_id','is_refund', 'goods_option_title', 'goods_id', 'goods_price', 'total', 'price', 'title', 'thumb', 'comment_status']);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class, 'goods_id', 'goods_id');
    }

    public static function getMyCommentList($status)
    {
        $list = self::select()->Where('comment_status', $status)->orderBy('id', 'desc')->get();
        return $list;
    }


}