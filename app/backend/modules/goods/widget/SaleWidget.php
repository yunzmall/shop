<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/9/9
 * Time: 17:39
 */

namespace app\backend\modules\goods\widget;

use app\backend\modules\goods\models\Sale;
use app\common\facades\Setting;
use app\common\models\Area;

class SaleWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'sale';

    public $code = 'promotion';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $set = Setting::get('shop');
        $shop['credit'] = $set['credit']?$set['credit']:'余额';
        $shop['credit1'] = $set['credit1']?$set['credit1']:'积分';
        $saleModel = new Sale();

        $sale = Sale::getList($this->goods->id);

        if ($sale) {
            $saleModel->setRawAttributes($sale->toArray());
        }

        $saleModel->is_store = $this->goods->plugin_id == 32 ? 1 : 0;

        $data = [
            'sale' => $saleModel,
            'set' => $shop
        ];
        
        return $data;
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}