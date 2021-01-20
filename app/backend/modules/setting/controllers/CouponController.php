<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/7/12
 * Time: 下午2:30
 */

namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\Url;
use app\common\models\notice\MessageTemp;

class CouponController extends BaseController
{
    public function index()
    {
        if(request()->ajax()){
            $coupon = Setting::get('shop.coupon');

            $requestModel = request()->coupon;
            if ($requestModel) {
                if (Setting::set('shop.coupon', $requestModel)) {
                    return $this->successJson('优惠券设置成功', Url::absoluteWeb('setting.shop.index'));
                } else {
                    $this->errorJson('优惠券设置失败');
                }
            }
            //原来的时间数据结构就是这样的
            for ($i = 0; $i <= 23; $i++) {
                $hourData[$i] = [
                    'key' => $i,
                    'name' => $i . ":00",
                ];
            }
            $temp_list = MessageTemp::getList()->toArray();
            $defaultTempId = MessageTemp::where('notice_type', 'expire')->where('is_default' ,1)->whereNull('deleted_at')->value('id');
            if($defaultTempId == $coupon['expire'] && $defaultTempId){
                $is_open = 1;
            }else{
                $is_open = 0;
            }
            return $this->successJson('请求接口成功',[
                'set' => $coupon,
                'hourData' => $hourData,
                'temp_list' => $temp_list,
                'is_open' => $is_open,
            ]);

        }
        return view('setting.shop.coupon');
    }
}