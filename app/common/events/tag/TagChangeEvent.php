<?php


namespace app\common\events\tag;

use app\common\events\Event;

class TagChangeEvent extends Event
{

    public $shop_tag;
    public $event_type;

    /*
     * event_type: add新增 delete删除 edit编辑
     */
    public function __construct($shop_tag, $event_type = 'edit')
    {
        $this->shop_tag = $shop_tag;
        $this->event_type = $event_type;
        \Log::debug('商城标签变动事件触发');
    }

    public function getEventTypeName()
    {
        $name = [
            'add' => '商城新增标签',
            'delete' => '商城删除标签',
            'edit' => '商城编辑标签'
        ];
        return $name[$this->event_type] ?: '';
    }


}