<?php


namespace app\backend\modules\withdraw\models;


class WithdrawModel extends \app\backend\models\Withdraw
{
    public function scopeSearch($query, $search)
    {
        if ($search['member_id']) {
            $query->where('member_id', $search['member_id']);
        }
        if (isset($search['status']) && $search['status'] != "") {
            $query->ofStatus($search['status']);
        }
        if ($search['withdraw_sn']) {
            $query->ofWithdrawSn($search['withdraw_sn']);
        }
        if ($search['type']) {
            $query->whereType($search['type']);
        }
        if ($search['pay_way']) {
            $query->where('pay_way', $search['pay_way']);
        }
        if ($search['searchtime']) {
            $range = [$search['time']['start'] / 1000, $search['time']['end'] / 1000];
            $query->whereBetween('created_at', $range);
        }
        if ($search['member']) {
            $query->whereHas('hasOneMember', function ($query) use ($search) {
                return $query->searchLike($search['member']);
            });
        }
        return $query;
    }

    public static function getPayWay()
    {
        $data = [
            [
                'value' => 'wechat',
                'title' => '提现到微信'
            ],
            [
                'value' => 'alipay',
                'title' => '提现到支付宝'
            ],
            [
                'value' => 'balance',
                'title' => '提现到余额'
            ],
            [
                'value' => 'manual',
                'title' => "提现到" . (\Setting::get('shop.lang.zh_cn.income.manual_withdrawal') ?: '手动打款')
            ],
        ];
        if (app('plugins')->isEnabled('eup_pay')) {
            $data[] = [
                'value' => 'eup_pay',
                'title' => '提现到EUP'
            ];
        }
        if (app('plugins')->isEnabled('huanxun')) {
            $data[] = [
                'value' => 'huanxun',
                'title' => '提现到银行卡'
            ];
        }

        if (app('plugins')->isEnabled('yee-pay')) {
            $data[] = [
                'value' => 'yee-pay',
                'title' => '提现到易宝代付'
            ];
        }
        if (app('plugins')->isEnabled('converge_pay')) {
            $data[] = [
                'value' => 'converge_pay',
                'title' => '提现到银行卡-HJ'
            ];
        }
        if (app('plugins')->isEnabled('high-light')) {
            $high_light = [
                [
                    'value' => 'high_light_wechat',
                    'title' => '提现到微信-高灯'
                ],
                [
                    'value' => 'high_light_alipay',
                    'title' => '提现到支付宝-高灯'
                ],
                [
                    'value' => 'high-light',
                    'title' => '提现到银行卡-高灯'
                ]
            ];
            $data = array_merge($data, $high_light);
        }
        if (app('plugins')->isEnabled('worker-withdraw')) {
            $worker_withdraw = [
                [
                    'value' => 'worker_withdraw_wechat',
                    'title' => '提现到微信-好灵工'
                ],
                [
                    'value' => 'worker_withdraw_alipay',
                    'title' => '提现到微信-好灵工'
                ],
                [
                    'value' => 'worker_withdraw_bank-light',
                    'title' => '提现到银行卡-好灵工'
                ]
            ];
            $data = array_merge($data, $worker_withdraw);
        }
        if (app('plugins')->isEnabled('eplus-pay')) {
            $data[] = [
                'value' => 'eplus_withdraw_bank',
                'title' => '提现到银行卡-智E+'
            ];
        }

        if (app('plugins')->isEnabled('jianzhimao-withdraw')) {
            $data[] = [
                'value' => 'jianzhimao_bank',
                'title' => '提现到兼职猫-银行卡'
            ];
        }

        if (app('plugins')->isEnabled('tax-withdraw')) {
            $data[] = [
                'value' => 'tax_withdraw_bank',
                'title' => '提现到' . TAX_WITHDRAW_DIY_NAME . '-银行卡'
            ];
        }

        return $data;
    }

}