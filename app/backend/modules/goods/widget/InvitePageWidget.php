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

use app\common\models\goods\InvitePage;

/**
 * 邀请页面(非插件)
 */
class InvitePageWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'invite_page';

    public $code = 'invite';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $goods_id = $this->goods->id;
        $invitePageModel = InvitePage::getDataByGoodsId($goods_id);

        $data = [];
        if ($invitePageModel) {
            $data = $invitePageModel->toArray();
        }
        
        return $data;
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}