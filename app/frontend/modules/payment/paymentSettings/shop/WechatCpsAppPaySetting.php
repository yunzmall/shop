<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/7/15
 * Time: 13:37
 */

namespace app\frontend\modules\payment\paymentSettings\shop;

use Yunshop\AggregationCps\services\SettingManageService;
use Yunshop\AggregationCps\services\WechatPayService;

class WechatCpsAppPaySetting extends BaseSetting
{
    public function canUse()
    {
        return $this->checkCpsAppWechatPay();
    }

    public function exist()
    {
        return $this->checkCpsAppWechatPay();
    }


    public function checkCpsAppWechatPay()
    {

        if (!is_null($event = \app\common\modules\shop\ShopConfig::current()->get('cps_wechat_app_pay_config'))) {
            $class = array_get($event, 'class');
            $function = array_get($event, 'function');
            $res = $class::$function();
            if ($res['result']){
                return true;
            }
        }

        return false;

    }


}