<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/26 下午1:44
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\frontend\modules\coupon\controllers;


use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\models\coupon\CouponUseLog;
use app\frontend\models\Member;
use app\frontend\modules\coupon\models\MemberCoupon;
use app\frontend\modules\coupon\services\CouponSendService;
use app\backend\modules\coupon\services\MessageNotice;
use Yunshop\GiftCouponFee\frontend\CouponFeeController;

class CouponTransferController extends ApiController
{
    public $memberModel;

    public function index()
    {

        $recipient = trim(\YunShop::request()->recipient);
        if (!$this->getMemberInfo()) {
            return  $this->errorJson('未获取到会员信息');
        }
        if (!Member::uniacid()->select('uid')->where('uid',$recipient)->first()) {
            return  $this->errorJson('被转让者不存在');
        }
        if ($this->memberModel->uid == $recipient) {
            return  $this->errorJson('转让者不能是自己');
        }


        $record_id = trim(\YunShop::request()->record_id);
        $_model = MemberCoupon::select('id','coupon_id','get_time')->where('id',$record_id)->with(['belongsToCoupon'])->first();
        if (!$_model) {
            return $this->errorJson('未获取到该优惠券记录ID');
        }
        //开启手续费后直接走手续费入口
        
        if (app('plugins')->isEnabled('gift-coupon-fee') && Setting::get("couponbase_setting")["switch"]==1)
        {

            return (new CouponFeeController())->index();

        }

//        if($_model->belongsToCoupon->get_type == 1 && $_model->belongsToCoupon->get_max != -1)
//        {
//            $person = MemberCoupon::uniacid()
//                    ->where(["coupon_id"=>$_model->coupon_id,"uid"=>$recipient])
//                    ->count();//会员已有数量
//            if($person + 1 > $_model->belongsToCoupon->get_max)
//            {
//                return  $this->errorJson('被转让者已达该优惠券领取上限');
//            }
//        }

        $couponService = new CouponSendService();
        $result = $couponService->sendCouponsToMember($recipient,[$_model->coupon_id],'5','',$this->memberModel->uid,strtotime($_model->get_time));
        if (!$result) {
            return $this->errorJson('转让失败：(写入出错)');
        }

        $result = MemberCoupon::where('id',$_model->id)->update(['used' => 1,'use_time' => time(),'deleted_at' => time()]);
        if (!$result) {
            return $this->errorJson('转让失败：(记录修改出错)');
        }

        $log_data = [
            'uniacid' => \YunShop::app()->uniacid,
            'member_id' => \YunShop::app()->getMemberId(),
            'detail' => '会员(ID为' . \YunShop::app()->getMemberId() . ')转赠一张优惠券(ID为' . $_model->coupon_id . ')，受赠会员(ID为' . $recipient . ')',
            'coupon_id' => $_model->coupon_id,
            'member_coupon_id' => $record_id,
            'type' => CouponUseLog::TYPE_TRANSFER
        ];
        $model = new CouponUseLog();
        $model->fill($log_data);
        $model->save();
//        '.$this->memberModel->uid.''.[$_model->coupon_id].'

        //发送获取通知
        //MessageNotice::couponNotice($_model->coupon_id,$recipient);

        return $this->successJson('转让成功,');
    }


    private function getMemberInfo()
    {
        return $this->memberModel = Member::select('uid')->where('uid',\YunShop::app()->getMemberId())->first();
    }






}
