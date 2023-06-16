<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/14
 * Time: 17:42
 */

namespace app\backend\modules\goods\widget;

use app\backend\modules\goods\models\Notice;
use app\common\models\Member;

//消息通知
class NoticeWidget  extends BaseGoodsWidget
{
    public $group = 'tool';

    public $widget_key = 'notice';

    public $code = 'notification';

    public function pluginFileName()
    {
        return 'goods';
    }

    public function getData()
    {

        $notices = Notice::getList($this->goods->id);
        if (!$notices->isEmpty()) {
            foreach ($notices as $notice) {
                $noticetype[] = $notice['type'];
                $uid = $notice['uid'];
            }
            $member = Member::select('uid', 'nickname', 'realname', 'mobile', 'avatar')
                ->where('uid', $uid)
                ->first();
        } else {
            $noticetype = [];
            $member = [];
        }
        return [
            'member'=> $member,
            'notice_type'=>$noticetype,
        ];
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}