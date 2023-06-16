<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/10
 * Time: 14:11
 */

namespace app\frontend\modules\member\controllers;


use app\common\components\ApiController;
use app\common\helpers\Client;
use app\frontend\models\Member;
use app\common\helpers\Cache;
use Illuminate\Support\Facades\Redis;

class MemberQrCodeController extends ApiController
{
    public function memberCode()
    {
        $uid = \YunShop::app()->getMemberId();
        $member = Member::select('uid','nickname','avatar')->where('uid',$uid)->first();
        return $this->successJson('获取成功',$member);
    }

    public function payCode()
    {
        $uid = \Yunshop::app()->getMemberId();
        $top = 9955;
        $bt = 326422624364;//fangbaocheng
        $time = time();
        $hash = hash('ripemd128', $uid . $top . md5($bt + $time));
        $code = $uid.'uu'.substr($hash,  0,  8) . substr($hash,  8,  4) . substr($hash, 12,  4) .
            substr($hash, 16,  2);
        $data = [
            'uid' => $uid,
            'time' => $time,
            'code' => $code,
            'img' => 'data:image/png;base64,' . base64_encode(\QrCode::format('png')->size(200)->generate($code)),
        ];
        Redis::setex('member_pay_id_'.$uid , 60, $code);
        Cache::put('member_pay_id_'.$uid, $data, 1);

        return $this->successJson('获取成功',$data);
    }

}