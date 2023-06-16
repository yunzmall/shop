<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/5/19
 * Time: 下午1:48
 */

namespace app\common\models\member;


use app\common\models\BaseModel;

class MemberPosition extends BaseModel
{
    public $table = 'yz_member_position';

    public $guarded = [''];

    /*
     * 获取地址数据表全部数据
     *
     * @return array */
    public static function getMemberLocation($member_id)
    {
        $model = static::where('member_id', $member_id)->first();

        return $model;
    }
}
