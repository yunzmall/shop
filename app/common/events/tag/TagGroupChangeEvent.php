<?php


namespace app\common\events\tag;

use app\common\events\Event;

class TagGroupChangeEvent extends Event
{

    public $shop_group;
    public $event_type;

    /*
     * event_type: add新增 delete删除 edit编辑
     */
    public function __construct($shop_tag_group, $event_type = 'edit')
    {
        $this->shop_group = $shop_tag_group;
        $this->event_type = $event_type;
        \Log::debug('商城标签组变动事件触发');
    }

    public function getEventTypeName()
    {
        $name = [
            'edit' => '商城编辑标签组'
        ];
        return $name[$this->event_type] ?: '';
    }


}