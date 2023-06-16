<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-08-02
 * Time: 11:38
 */

namespace app\common\models\member;


use app\common\models\BaseModel;

class MemberCancelSet extends BaseModel
{
    public $table = 'yz_member_cancel_set';
    public $guarded = [''];
    public $timestamps = true;
}