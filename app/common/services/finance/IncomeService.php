<?php
/**
 * Author:
 * Date: 2017/6/8
 * Time: 下午5:49
 */

namespace app\common\services\finance;


use app\common\facades\Setting;

class IncomeService
{
    private static $payWay = [
        'balance',
        'wechat',
        'alipay',
        'manual',
        'huanxun',
        'eup_pay',
        'yop_pay',
        'yee_pay',
        'converge_pay',
        'high_light_wechat',
        'high_light_alipay',
        'high_light_bank',
        'worker_withdraw_wechat',
        'worker_withdraw_alipay',
        'worker_withdraw_bank',
        'eplus_withdraw_bank',
        'silver_point',
        'jianzhimao_bank',
        'tax_withdraw_bank'
    ];

    private static $payWayName = [
        'balance'                => "提现到余额",
        'wechat'                 => "提现到微信",
        'alipay'                 => "提现到支付宝",
        'manual'                 => "提现到手动打款",
        'huanxun'                => "提现到银行卡",
        'eup_pay'                => "提现到EUP",
        'yop_pay'                => "提现到易宝",
        'converge_pay'           => "提现到银行卡-HJ",
        'yee_pay'                => "提现到易宝代付",
        'high_light_wechat'      => "提现到微信-高灯",
        'high_light_alipay'      => "提现到支付宝-高灯",
        'high_light_bank'        => "提现到银行卡-高灯",
        'worker_withdraw_wechat' => "提现到微信-好灵工",
        'worker_withdraw_alipay' => "提现到支付宝-好灵工",
        'worker_withdraw_bank'   => "提现到银行卡-好灵工",
        'eplus_withdraw_bank'    => "提现到银行卡(智E+)",
        'silver_point'           => "提现到银典支付",
        'jianzhimao_bank'        => "提现到兼职猫-银行卡",
        'tax_withdraw_bank'      => "提现到税惠添薪-银行卡",
    ];

    public function withdrawButton($incomeType = 'default')
    {
        switch ($incomeType) {
            case 'StoreCashier':
                return $this->storeCashierButton();
            case 'StoreWithdraw':
                return $this->storeWithdrawButton();
            case 'StoreBossWithdraw':
                return $this->storeBossWithdrawButton();
            case 'HotelCashier':
                return $this->hotelCashierWithdrawButton();
            case 'hotel_withdraw':
                return $this->hotelWithdrawrButton();
            default:
                return $this->defaultButton();
        }
    }

    /**
     * 门店收银台提现方式按钮组
     *
     * @return array
     */
    private function storeCashierButton()
    {
        return $this->customButton('StoreCashier');
    }

    /**
     * 门店提现方式按钮组
     *
     * @return array
     */
    private function storeWithdrawButton()
    {
        return $this->customButton('StoreWithdraw');
    }

    /**
     * 连锁店现方式按钮组
     *
     * @return array
     */
    private function storeBossWithdrawButton()
    {
        return $this->customButton('StoreBossWithdraw');
    }

    /**
     * 酒店收银台方式按钮组
     *
     * @return array
     */
    private function hotelCashierWithdrawButton()
    {
        return $this->customButton('hotel_cashier_withdraw');
    }

    /**
     * 酒店方式按钮组
     *
     * @return array
     */
    private function hotelWithdrawrButton()
    {
        return $this->customButton('hotel_withdraw');
    }

    /**
     * 通过收入类型获取对应开启的提现方式按钮组
     *
     * @param string $incomeType
     *
     * @return array
     */
    private function customButton($incomeType)
    {
        if (!$this->incomeCustomStatus($incomeType)) {
            return $this->defaultButton();
        }
        return $this->_customButton($incomeType);
    }

    /**
     * @param string $incomeType
     *
     * @return array
     */
    private function _customButton($incomeType)
    {
        $defaultButton = ['service_switch' => $this->serviceSwitch()];

        foreach ($this->incomeCustomSet($incomeType) as $item) {
            if (in_array($item, static::$payWay)) {
                $defaultButton[$item] = [
                    'name'       => $this->buttonName($item),
                    'value'      => $item,
                    'other_name' => $this->otherButtonName($item),
                ];
            }
        }
        return $defaultButton;
    }

    /**
     * 提现方式按钮，读取提现设置中开启的提现方式，返回按钮格式
     *
     * @return array
     */
    private function defaultButton()
    {
        $defaultButton = ['service_switch' => $this->serviceSwitch()];
        foreach ($this->withdrawSet() as $key => $item) {
            if (in_array($key, static::$payWay) && $item && $this->buttonEnabled($key)) {
                $defaultButton[$key] = [
                    'name'       => $this->buttonName($key),
                    'value'      => $key,
                    'other_name' => $this->otherButtonName($key),
                    'extra_data' => $this->extraData($key),
                ];
            }
        }
        return $defaultButton;
    }

    private function extraData($key)
    {
        $return_data = [];
        if (!is_null($data = \app\common\modules\shop\ShopConfig::current()->get('withdraw_list_extra_data.' . $key))) {
            if (($class = $data['class']) && ($function = $data['function']) && method_exists($class, $function)) {
                $return_data = $class::$function();
            }
        }
        return $return_data;
    }

    private function buttonName($key)
    {
        $balance = Setting::get('shop.shop');
        $set = Setting::get('shop.lang.zh_cn.income');
        if ($set['name_of_withdrawal']) {
            $name = $set['name_of_withdrawal'] ?: '';
        } else {
            $name = '提现';
        }

        $high_light_name = '高灯提现';
        if (app('plugins')->isEnabled('high-light')) {
            $high_light_name = \Yunshop\HighLight\services\SetService::getDiyName();
        }

        $manual_withdrawal = Setting::get('shop.lang.zh_cn.income.manual_withdrawal');
        switch ($key) {
            case 'balance':
                return $name . '到 ' . ($balance['credit'] ?: '余额');
            case 'wechat':
                return $name . '到 微信';
            case 'alipay':
                return $name . '到 支付宝';
            case 'manual':
                return $name . '到 ' . ($manual_withdrawal ?: '手动打款');
            case 'huanxun':
                return $name . '到 银行卡';
            case 'eup_pay':
                return $name . '到 EUP';
            case 'yop_pay':
                return $name . '到 易宝';
            case 'converge_pay':
                return $name . '到 银行卡-HJ';
            case 'yee_pay':
                return $name . '到 易宝代付';
            case 'high_light_wechat':
                return $name . '到 微信-' . $high_light_name;
            case 'high_light_alipay':
                return $name . '到 支付宝-' . $high_light_name;
            case 'high_light_bank':
                return $name . '到 银行卡-' . $high_light_name;
            case 'eplus_withdraw_bank':
                return $name . '到 银行卡-智E+';
            case 'silver_point':
                return $name . '到 银典支付';
            case 'jianzhimao_bank':
                return $name . '到 兼职猫-银行卡';
            case 'tax_withdraw_bank':
                $div_name = '税惠添薪';
                if (app('plugins')->isEnabled('tax-withdraw')) {
                    $div_name = TAX_WITHDRAW_DIY_NAME;
                }
                return $name . '到 ' . $div_name . '-银行卡';
            default:
                return '';
        }
    }

    private function otherButtonName($key)
    {
        if (app('plugins')->isEnabled('high-light')) {
            $high_light_name = \Yunshop\HighLight\services\SetService::getDiyName();
        }
        $balance = Setting::get('shop.shop');
        $manual_withdrawal = Setting::get('shop.lang.zh_cn.income.manual_withdrawal');
        switch ($key) {
            case 'balance':
                return $balance['credit'] ?: '余额';
            case 'wechat':
                return '微信';
            case 'alipay':
                return '支付宝';
            case 'manual':
                return $manual_withdrawal ?: '手动打款';
            case 'huanxun':
                return '银行卡';
            case 'eup_pay':
                return 'EUP';
            case 'yop_pay':
                return '易宝';
            case 'converge_pay':
                return '银行卡-HJ';
            case 'yee_pay':
                return '易宝代付';
            case 'high_light_wechat':
                return '微信-' . (isset($high_light_name) ? $high_light_name : '高灯');
            case 'high_light_alipay':
                return '支付宝-' . (isset($high_light_name) ? $high_light_name : '高灯');
            case 'high_light_bank':
                return '银行卡-' . (isset($high_light_name) ? $high_light_name : '高灯');
            case 'eplus_withdraw_bank':
                return '银行卡-智E+';
            case 'silver_point':
                return '银典支付';
            case 'jianzhimao_bank':
                return '兼职猫-银行卡';
            case 'tax_withdraw_bank':
                $div_name = '税惠添薪';
                if (app('plugins')->isEnabled('tax-withdraw')) {
                    $div_name = TAX_WITHDRAW_DIY_NAME;
                }
                return $div_name . '-银行卡';
            default:
                return '';
        }
    }

    private function buttonEnabled($key)
    {
        switch ($key) {
            case 'balance':
            case 'wechat':
            case 'alipay':
            case 'manual':
            case 'yop_pay':
                return true;
            case 'silver_point':
                if (!app('plugins')->isEnabled('silver-point-pay')) {
                    return false;
                }
                return Setting::get('silver-point-pay.set.plugin_enable') && Setting::get('silver-point-pay.set.behalf_enable');
            case 'huanxun':
                if (!app('plugins')->isEnabled('huanxun')) {
                    return false;
                }
                return true;
            case 'eup_pay':
                if (!app('plugins')->isEnabled('eup-pay')) {
                    return false;
                }
                return true;
            case 'converge_pay':
                if (!app('plugins')->isEnabled('converge_pay')) {
                    return false;
                }
                return true;
            case 'yee_pay':
                if (!app('plugins')->isEnabled('yee-pay') || !\Yunshop\YeePay\services\SetService::getStatus()) {
                    return false;
                }
                return true;
            case 'worker_withdraw_wechat':
                return self::workerWithdrawEnable(2);
            case 'worker_withdraw_alipay':
            case 'worker_withdraw_bank':
                return self::workerWithdrawEnable(1);
            case 'eplus_withdraw_bank':
                return app('plugins')->isEnabled('eplus-pay') && \Yunshop\EplusPay\services\SettingService::usable();
            case 'high_light_wechat':
            case 'high_light_alipay':
            case 'high_light_bank':
                if (!app('plugins')->isEnabled('high-light') || !\Yunshop\HighLight\services\SetService::getStatus()) {
                    return false;
                }
                return true;
            case 'jianzhimao_bank':
                if (!app('plugins')->isEnabled('jianzhimao-withdraw') || !Setting::get('jianzhimao-withdraw.set.plugin_enable')) {
                    return false;
                }
                return true;
            case 'tax_withdraw_bank':
                if (!app('plugins')->isEnabled('tax-withdraw') || !Setting::get('tax-withdraw.set.plugin_enable')) {
                    return false;
                }
                return true;
            default:
                return false;
        }
    }


    /**
     * @return bool
     * 好灵工提现是否可用
     */
    public static function workerWithdrawEnable($re_type)
    {
        return app('plugins')->isEnabled('worker-withdraw') && \Yunshop\WorkerWithdraw\services\SettingService::usable(
                [],
                $re_type
            );
    }

    /**
     * 收入类型是否开启自定义提现方式
     *
     * @param string $incomeType
     *
     * @return bool
     */
    private function incomeCustomStatus($incomeType)
    {
        return !!Setting::get("withdraw.{$incomeType}.withdraw_type");
    }

    /**
     * 收入类型自定义提现方式设置
     *
     * @param string $incomeType
     *
     * @return array
     */
    private function incomeCustomSet($incomeType)
    {
        return Setting::get("withdraw.{$incomeType}.withdraw_method");
    }

    /**
     * 提现设置
     *
     * @return array
     */
    private function withdrawSet()
    {
        return Setting::get('withdraw.income');
    }

    /**
     * 是否显示劳务税
     *
     * @return int
     */
    private function serviceSwitch()
    {
        return Setting::get('withdraw.income.service_switch') ?: 0;
    }
}
