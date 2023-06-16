<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/7
 * Time: 10:43
 */

namespace app\payment\controllers;


use app\common\components\BaseController;
use app\common\helpers\Url;

class CashierqrcodeController extends BaseController
{
    /**普通二维码跳转微信小程序
     * dev4.yunzmall.com/addons/yun_shop/payment/cashierqrcode/returnUrl.php?scene=store_id=41,mid=452
     * @return bool
     */
    public function returnUrl()
    {
        $data = [];
        $scene = request()->scene;
        $array = explode(',',$scene);
        foreach ($array as $value) {
            $b = explode('=',$value);
            $data[$b[0]] = $b[1];
        }
        redirect(Url::absoluteApp('cashier_pay/'.$data['store_id'], $data))->send();
        return true;
    }

}