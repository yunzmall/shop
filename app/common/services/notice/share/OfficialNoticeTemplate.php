<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/7
 * Time: 20:10
 */

namespace app\common\services\notice\share;


use app\common\models\notice\MessageTemp;

Trait OfficialNoticeTemplate
{
    public $temp_id;
    public $template_id;
    public $messageTemp;
    public $url;

    public function getTemplate($name)
    {
        $this->temp_id = \Setting::get('shop.notice')[$name];
        $this->messageTemp =  MessageTemp::find($this->temp_id);
        $this->template_id = $this->messageTemp->template_id;
        $this->url = $this->messageTemp->news_link;
    }
}