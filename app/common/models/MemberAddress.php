<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/2
 * Time: 下午4:45
 */

namespace app\common\models;



class MemberAddress extends BaseModel
{
    protected $table = 'mc_member_address';
    protected $guarded = ['street'];

    public $timestamps = false;

}