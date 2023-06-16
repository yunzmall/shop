<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/3/30
 * Time: 17:06
 */

namespace app\common\payment\setting\alipay;

use app\common\payment\setting\BasePaymentSetting;
use app\common\payment\types\BasePaymentTypes;
use app\frontend\modules\member\services\factory\MemberFactory;
use app\frontend\modules\order\payment\types\OrderPaymentTypes;
use Yunshop\AlipayPeriodDeduct\services\GoodsService;
use Yunshop\AlipayPeriodDeduct\services\SettingService;

class AlipayPeriodDeductSetting extends BasePaymentSetting
{
    public function __construct(BasePaymentTypes $basePaymentTypes)
    {
        parent::__construct($basePaymentTypes);
        if (!app('plugins')->isEnabled('alipay-period-deduct')
            || !SettingService::pluginSwitch()
            || !$basePaymentTypes instanceof OrderPaymentTypes
        ) {
            $this->paymentTypes->filterCode[] = 'alipayPeriodDeduct';//黑名单
            return;
        }

        $orders = $this->paymentTypes->getOrders();
        $order = $orders->first();
        $orderGoods = $order->hasManyOrderGoods->first();
        if ($orders->count() != 1
            || ($order->orderGoods->count() != 1)
            || ($orderGoods->total != 1)
            || !GoodsService::checkPendantOpen($orderGoods->goods_id)
        ) {
            $this->paymentTypes->filterCode[] = 'alipayPeriodDeduct';//黑名单
            return;
        }

    }

    public function canUse()
    {
        return (request()->type == MemberFactory::LOGIN_APP_CPS ||
                (request()->type == 5 && request()->scope == 'tjpcps')
            );
//            && $this->storeSetting('alipayPeriodDeduct');
    }
}