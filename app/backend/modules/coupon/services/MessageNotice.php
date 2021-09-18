<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/3/13
 * Time: 11:59
 */
namespace app\backend\modules\coupon\services;

use app\common\models\Goods;
use app\common\services\MessageService;
use app\common\facades\Setting;
use app\common\models\notice\MessageTemp;
use app\common\models\Coupon;
use app\backend\modules\member\models\Member;
use app\common\services\SystemMsgService;
use Yunshop\StoreCashier\common\models\Store;

class MessageNotice extends MessageService
{
    public static function couponNotice($couponDate,$memberId)
    {
        //【系统消息通知】
        (new SystemMsgService())->couponNotice($couponDate);

        $couponNotice = Setting::get('coupon.coupon_notice');
        $member = Member::getMemberInfoById($memberId);
//        dump(Coupon::getPromotionMethod($couponDate->id));exit();
        $temp_id = $couponNotice;
        if (!$temp_id) {
            return false;
        }
        static::messageNotice($temp_id,$couponDate, $member);
        return true;
    }
    public static function messageNotice($temp_id, $couponId, $member, $uniacid = '')
    {
        $couponDate = Coupon::getCouponById($couponId);

        //优惠方式
        $coupon_mode = Coupon::getPromotionMethod($couponDate->id);
        if($coupon_mode['type'] == 1) {
            $coupon_mode['content'] = "立减".floatval($coupon_mode['mode'])."元";
        } elseif ($coupon_mode['type'] == 2) {
            $coupon_mode['content'] = "打".floatval($coupon_mode['mode'])."折";
        }

        $scope = '';
        //适用范围
        $coupon_scope = Coupon::getApplicableScope($couponDate->id);
        if($coupon_scope['type'] == 0) {
            $scope = "全类适用";
        } elseif ($coupon_scope['type'] == 1) {
            $category_name = implode(',',Coupon::where('id', '=', $couponDate->id)->value('categorynames'));
            $scope = "".$category_name."类商品可用";
        } elseif ($coupon_scope['type'] == 2) {
            $goods_name = implode(',',Coupon::where('id', '=', $couponDate->id)->value('goods_names'));
            $scope = "".$goods_name."商品可用";
        } elseif ($coupon_scope['type'] == 4 || $coupon_scope['type'] == 5) {//4 多门店可用  5 单门店可用
            $goods_name = implode(',',Coupon::where('id', '=', $couponDate->id)->value('storenames'));
            $scope = "".$goods_name."门店可用";
        } elseif ($coupon_scope['type'] == 9) {
            $use_condition = unserialize(Coupon::where('id', '=', $couponDate->id)->value('use_conditions'));
            if (empty($use_condition)) {
                $scope = "无适用范围";
            }
            if (app('plugins')->isEnabled('store-cashier')) {
                if ($use_condition['is_all_store'] == 1) {
                    $scope .= "全部门店、";
                } else {
                    $scope = implode(',', Store::uniacid()->whereIn('id', $use_condition['store_ids'])->pluck('store_name')->all()).'、';
                }
            }
            if ($use_condition['is_all_good'] == 1) {
                $scope .= "平台自营商品";
            } else {
                $scope = implode(',', Goods::uniacid()->whereIn('id', $use_condition['good_ids'])->pluck('title')->all());
            }
        }

        //结束时间
        $coupon_time_end = Coupon::getTimeLimit($couponDate->id);
        if($coupon_time_end['type'] == 0) {
            if ($coupon_time_end['time_end'] == 0) {
                $time_end = "无时间限制";
            } else {
                $time_end = date('Y-m-d H:i:s',(strtotime('+'.$coupon_time_end['time_end'].'day',time())));
            }

        } elseif ($coupon_time_end['type'] == 1) {

            $time_end = $coupon_time_end['time_end'];
        }

        //优惠券使用条件
        if($couponDate->enough == 0) {
            $coupon_enough = "无门槛";
        } else {
            $coupon_enough = "满".$couponDate->enough."元可用";
        }


        $params = [
            ['name' => '昵称', 'value' => $member['nickname']],
            ['name' => '优惠券名称', 'value' => $couponDate->name],
            ['name' => '优惠券使用范围', 'value' => $scope],
            ['name' => '优惠券使用条件', 'value' => $coupon_enough],
            ['name' => '优惠方式', 'value' => $coupon_mode['content']],
            ['name' => '过期时间', 'value' => $time_end],
            ['name' => '获得时间', 'value' => date('Y-m-d H:i:s', time())],
        ];
        $msg = MessageTemp::getSendMsg($temp_id, $params);
        if (!$msg) {
            return false;
        }

        $news_link = MessageTemp::find($temp_id)->news_link;
        $news_link = $news_link ?:'';

        MessageService::notice(MessageTemp::$template_id, $msg, $member->uid, $uniacid,$news_link);
    }
}