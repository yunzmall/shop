<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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