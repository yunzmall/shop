<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/25 下午6:38
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\backend\modules\coupon\controllers;


use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\models\notice\MessageTemp;
use app\common\facades\Setting;
use app\common\helpers\Url;

class BaseSetController extends UploadVerificationBaseController
{

    public function see()
    {
        return view('coupon.base_set')->render();

//        $coupon_set = \Setting::getByGroup('coupon');
//
//        $temp_list = MessageTemp::getList();
//        return view('coupon.base_set', [
//            'coupon' => $coupon_set,
//            'temp_list' => $temp_list,
//        ])->render();
    }

    public function seeData()
    {
        $coupon_set = \Setting::getByGroup('coupon');
        $coupon_set['shopping_share']['banner_url'] = yz_tomedia($coupon_set['shopping_share']['banner']);
        $coupon_notice = \app\common\models\notice\MessageTemp::getIsDefaultById($coupon_set['coupon_notice']);
        $temp_list = MessageTemp::getList();
        $data = [
            'coupon' => $coupon_set,
            'temp_list' => $temp_list,
            'coupon_notice'    => $coupon_notice
        ];
        return $this->successJson('ok',$data);
    }

    /**
     * 保存设置
     * @return mixed|string
     */
    public function store()
    {
        $requestData = request()->coupon;
//        dump($requestData);exit();
        if ($requestData) {
            foreach ($requestData as $key => $item) {
                \Setting::set('coupon.' . $key, $item);
            }
            return $this->successJson("设置保存成功");
        }
        return $this->see();
    }

}
