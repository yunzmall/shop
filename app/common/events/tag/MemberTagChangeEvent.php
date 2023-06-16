<?php


namespace app\common\events\tag;

use app\common\events\Event;

class MemberTagChangeEvent extends Event
{

    public $shop_tag;
    public $uid;
    public $event_type;

    /*
     * event_type: add新增 delete删除
     */
    public function __construct($shop_tag, $member_id, $event_type = 'add')
    {
        $this->shop_tag = $shop_tag;
        $this->uid = $member_id;
        $this->event_type = $event_type;
        \Log::debug('会员标签变动事件触发');
    }

    public function getEventTypeName()
    {
        $name = [
            'add' => '会员新增标签',
            'delete' => '会员删除标签'
        ];
        return $name[$this->event_type] ?: '';
    }


}