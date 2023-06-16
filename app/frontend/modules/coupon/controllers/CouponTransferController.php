<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/26 下午1:44
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 ****************************************************************/

namespace app\frontend\modules\coupon\controllers;


use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\models\coupon\CouponUseLog;
use app\frontend\models\Member;
use app\frontend\modules\coupon\models\MemberCoupon;
use app\frontend\modules\coupon\services\CouponSendService;
use app\backend\modules\coupon\services\MessageNotice;
use Illuminate\Support\Facades\DB;
use Yunshop\GiftCouponFee\frontend\CouponFeeController;

class CouponTransferController extends ApiController
{
    public $memberModel;

    public function index()
    {
        $coupon_set = \Setting::getByGroup('coupon');
        $recipient = trim(\YunShop::request()->recipient);
        $record_id = trim(\YunShop::request()->record_id);
        $transfer_num = trim(\YunShop::request()->transfer_num);
        if (!$this->getMemberInfo()) {
            return  $this->errorJson('未获取到会员信息');
        }
        if (!Member::uniacid()->select('uid')->where('uid',$recipient)->first()) {
            return  $this->errorJson('被转让者不存在');
        }
        if ($this->memberModel->uid == $recipient) {
            return  $this->errorJson('转让者不能是自己');
        }
        $_model = MemberCoupon::select('id','coupon_id','get_time','uid')->where('id',$record_id)->where('uid',\YunShop::app()->getMemberId())->with(['belongsToCoupon'])->first();
        if (!$_model) {
            return $this->errorJson('未获取到该优惠券记录ID');
        }
        //开启手续费后直接走手续费入口
        if (app('plugins')->isEnabled('gift-coupon-fee') && Setting::get("couponbase_setting")["switch"]==1 && $coupon_set['transfer_num'] != 1) {
            return (new CouponFeeController())->index();
        }
        if (!$coupon_set['transfer_num'] && $transfer_num > 1) {
            return $this->errorJson('未开启多张转赠功能！');
        }
        if ($coupon_set['transfer_num']) {
            $this->judgeNum($transfer_num,$_model);
            if ($coupon_set['transfer_choice'] == 1) {
                //最新日期
                $finder = MemberCoupon::uniacid()
                    ->where(['used'=>0,'is_member_deleted'=>0,'is_expired'=>0,'uid'=>$_model->uid,'coupon_id'=>$_model->coupon_id])
                    ->orderBy('get_time','desc')
                    ->limit($transfer_num)
                    ->get();
            } else {
                //快过期
                $finder = MemberCoupon::uniacid()
                    ->where(['used'=>0,'is_member_deleted'=>0,'is_expired'=>0,'uid'=>$_model->uid,'coupon_id'=>$_model->coupon_id])
                    ->orderBy('get_time','asc')
                    ->limit($transfer_num)
                    ->get();
            }
            DB::beginTransaction();
            try {
                foreach ($finder as $find) {
                    //因为需要继承获得时间，所以此处遍历
                    $couponService = new CouponSendService();
                    $result = $couponService->sendCouponsToMember($recipient, [$_model->coupon_id], '5', '', $this->memberModel->uid, strtotime($find->get_time));
                    if (!$result) {
                        throw new AppException('转让失败：(写入出错)');
                    }
                    $this->handleTransfer($_model->coupon_id, $find->id, $recipient);
                }
                DB::commit();
                return $this->successJson('转让成功,');
            } catch (\Exception $e) {
                DB::rollBack();
                throw new AppException($e->getMessage());
            }
        }
        $couponService = new CouponSendService();
        $result = $couponService->sendCouponsToMember($recipient,[$_model->coupon_id],'5','',$this->memberModel->uid,strtotime($_model->get_time));
        if (!$result) {
            return $this->errorJson('转让失败：(写入出错)');
        }
        $this->handleTransfer($_model->coupon_id,$record_id,$recipient);
        return $this->successJson('转让成功,');
    }

    /**
     * @param $coupon_id
     * @param $record_id
     * @param $recipient
     * @return \Illuminate\Http\JsonResponse
     * 减去持有者优惠券&记录
     */
    private function handleTransfer($coupon_id,$record_id,$recipient)
    {
        $result = MemberCoupon::where('id',$record_id)->update(['used' => 1,'use_time' => time()]);
        if (!$result) {
            return $this->errorJson('转让失败：(记录修改出错)');
        }

        $log_data = [
            'uniacid' => \YunShop::app()->uniacid,
            'member_id' => \YunShop::app()->getMemberId(),
            'detail' => '会员(ID为' . \YunShop::app()->getMemberId() . ')转赠一张优惠券(ID为' . $coupon_id . ')，受赠会员(ID为' . $recipient . ')',
            'coupon_id' => $coupon_id,
            'member_coupon_id' => $record_id,
            'type' => CouponUseLog::TYPE_TRANSFER
        ];
        $model = new CouponUseLog();
        $model->fill($log_data);
        $model->save();
    }

    /**
     * @param $transfer_num
     * @throws AppException
     * 判断输入转赠数量
     */
    private function judgeNum($transfer_num,$_model)
    {
        if($transfer_num < 0 || !is_numeric($transfer_num) || floor($transfer_num) != $transfer_num)
        {
            throw new AppException('请输入正确张数');
        }
        $coupons_num = MemberCoupon::uniacid()
            ->where(['used'=>0,'is_member_deleted'=>0,'is_expired'=>0,'uid'=>$_model->uid,'coupon_id'=>$_model->coupon_id])
            ->count();
        if($transfer_num > $coupons_num)
        {
            throw new AppException('数量不足，请重新输入');
        }
    }

    private function getMemberInfo()
    {
        return $this->memberModel = Member::select('uid')->where('uid',\YunShop::app()->getMemberId())->first();
    }






}
