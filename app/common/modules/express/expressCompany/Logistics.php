<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2021/1/28
 * Time: 15:20
 */

namespace app\common\modules\express\expressCompany;


interface Logistics {
    public function getTraces($comCode, $expressSn, $orderSn,$order_id);
}