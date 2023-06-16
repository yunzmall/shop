<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/30
 * Time: 18:00
 */

namespace app\common\events\member;


use app\common\events\Event;
use app\common\models\MemberShopInfo;

class MemberLevelDemotionEvent extends Event
{
    protected $memberModel;

    public function __construct(MemberShopInfo $memberModel)
    {
        $this->memberModel = $memberModel;
    }

    public function getMemberModel()
    {
        return $this->memberModel;
    }
}