<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/2
 * Time: 下午4:45
 */

namespace app\common\models;

class MemberCertified extends BaseModel
{
    protected $table = 'yz_member_certified';//实名认证记录表

    public $timestamps = false;

    public static function insertData($data)
    {
        self::uniacid()->insert($data);
    }

    public static function updateOrderId($id,$order_id,$remark='')
    {
        self::uniacid()->where('id',$id)->update(['order_id'=>$order_id,'remark'=>$remark,'updated_at'=>time()]);
    }

    public static function getFirstData($member_id)
    {
        return self::uniacid()->select('realname','idcard','id','order_id')->where('member_id',$member_id)->orderBy('updated_at','desc')->orderBy('created_at','desc')->first();
    }

}