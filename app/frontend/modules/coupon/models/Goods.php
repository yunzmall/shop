<?php

namespace app\frontend\modules\coupon\models;

class Goods extends \app\common\models\Goods
{

    protected $appends = ['thumb_image'];

    public function getThumbImageAttribute ()
    {
        return yz_tomedia($this->thumb);
    }

}