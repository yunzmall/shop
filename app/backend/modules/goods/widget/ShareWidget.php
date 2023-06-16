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

use app\backend\modules\goods\models\Share;
use app\common\helpers\ImageHelper;

/**
 * 分享关注(非插件)
 */
class ShareWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'share';

    public $code = 'share';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $share = new Share();
        if ($this->goods->id && Share::getInfo($this->goods->id)) {
            $share = Share::getInfo($this->goods->id);
        }

        $data = [];

        if ($share) {
            $data = $share->toArray();
            $data['share_thumb'] = yz_tomedia($data['share_thumb']);
        }
        
        return $data;
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}