<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/10
 * Time: 14:09
 */

namespace app\backend\modules\goods\widget;



use app\backend\modules\goods\models\GoodsTradeSet;

class TradeSetWidget extends BaseGoodsWidget
{
    public $group = 'tool';

    public $widget_key = 'trade_set';

    public $code = 'trade';

    public function pluginFileName()
    {
        return 'goods';
    }

    /**
     * 权限判断
     * @return boolean
     */
    public function usable()
    {
        if (app('plugins')->isEnabled('address-code')) {
            return true;
        }
        return false;
    }

    public function getData()
    {
        $data['hide_status'] = 0;
        $data['auto_send'] = 0;
        $data['begin_hide_day'] = 1;
        $data['begin_hide_time'] = '00:00';
        $data['end_hide_day'] = 0;
        $data['end_hide_time'] = '00:00';
        $data['auto_send_day'] = 1;
        $data['auto_send_time'] = '00:00';
        $data['arrived_time'] = '18:00';
        $data['arrived_day'] = 1;
        $data['arrived_word'] = '';
        if (!$this->goods) {
            return $data;
        }
        $model = GoodsTradeSet::uniacid()->where('goods_id', $this->goods->id)->first();
        if ($model) {
            $data['hide_status'] = (int)$model->hide_status;
            $data['auto_send'] = (int)$model->auto_send;
            $data['begin_hide_day'] = $model->begin_hide_day;
            $data['begin_hide_time'] = $model->begin_hide_time;
            $data['begin_hide_day'] = $model->begin_hide_day;
            $data['end_hide_day'] = (int)$model->end_hide_day;
            $data['end_hide_time'] = $model->end_hide_time;
            $data['auto_send_day'] = $model->auto_send_day;
            $data['auto_send_time'] = $model->auto_send_time;
            $data['arrived_day'] = $model->arrived_day;
            $data['arrived_time'] = $model->arrived_time;
            $data['arrived_word'] = $model->arrived_word;
        }
        return $data;
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}