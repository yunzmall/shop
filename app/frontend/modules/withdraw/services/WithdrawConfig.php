<?php

namespace app\frontend\modules\withdraw\services;

use app\common\modules\shop\CommonConfig;

class WithdrawConfig extends CommonConfig
{
    public static $current;

    static public function current()
    {
        if (!isset(static::$current)) {
            static::$current = new static();
        }
        return static::$current;
    }

    protected function _getItems()
    {
        $result = [
            'withdraw_apply' => [
                [
                    'income_type' => 'common',
                    'class' => function () {
                        return new \app\frontend\modules\withdraw\services\WithdrawApplySetService();
                    }
                ],
            ],
        ];
        $this->items = $result;
        return $this->items;
    }
}