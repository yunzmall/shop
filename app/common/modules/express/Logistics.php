<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2021/1/12
 * Time: 17:55
 */

namespace app\common\modules\express;


use app\common\models\Brand;
use app\common\models\LogisticsSet;
use app\common\modules\express\expressCompany\YqLogistics;
use app\common\modules\express\expressCompany\KdnLogistics;

class Logistics
{

    public function getTraces($comCode, $expressSn, $orderSn = '',$order_id = '')
    {
        $set = LogisticsSet::uniacid()->first();//查询物流配置
        if (!$set){
            return json_encode(array('result'=>'error','resp'=>'请配置物流设置信息'));
        }
        $data = unserialize($set->data);
        switch ($set->type){
            case 1:
                $result = new KdnLogistics($data);
                 break;
            case 2:
                $result = new YqLogistics($data);
                break;
        }

        $result =  $result->getTraces($comCode, $expressSn, $orderSn,$order_id);
        return $result;
    }

}

