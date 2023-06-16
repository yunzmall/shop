<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/16
 * Time: 14:53
 */

namespace app\backend\modules\goods\widget;

use app\common\models\goods\GoodsService;

class ServiceWidget extends BaseGoodsWidget
{
    public $group = 'tool';

    public $widget_key = 'service';

    public $code = 'service';

    public function pluginFileName()
    {
        return 'goods';
    }

    public function getData()
    {
        $data['is_automatic'] = 0;
        $data['starttime'] = time();
        $data['endtime'] = strtotime('1 month');
        $data['time_type'] = 0;
        $data['loop_date_start'] = time();
        $data['loop_date_end'] = strtotime('1 month');
        $data['loop_time_up'] = '';
        $data['loop_time_down'] = '';
        $data['auth_refresh_stock'] = 1;
        $data['original_stock'] = 9999;
        if (is_null($this->goods)) {
            return $data;
        }
        $service = GoodsService::select()->ofGoodsId($this->goods->id)->first();
        $data['is_automatic'] = $service->is_automatic;
        $data['time_type'] = $service->time_type;
        $data['loop_date_start'] = $service->loop_date_start?:time();
        $data['loop_date_end'] = $service->loop_date_end?:strtotime('+1 month');
        $data['loop_time_up'] = $service->loop_time_up?:'';
        $data['loop_time_down'] = $service->loop_time_down?:'';
        $data['auth_refresh_stock'] = $service->auth_refresh_stock;
        $data['starttime'] = $service->on_shelf_time?:time();
        $data['endtime'] = $service->lower_shelf_time?:strtotime('+1 month');
        $data['original_stock'] = $service->original_stock;
        return $data;
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}