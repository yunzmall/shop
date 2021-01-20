<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/5
 * Time: 17:48
 */

namespace app\common\services\notice\share;


use app\common\models\MiniTemplateCorresponding;
use app\common\models\notice\MinAppTemplateMessage;

Trait MiniNoticeTemplate
{
    public $temp_id = 0;
    public $temp_open = 0;
    public $temp_title = "";

    public function getTemplate($name)
    {

        $corres_id = MiniTemplateCorresponding::uniacid()->where("template_name",$name)->value("template_id");

        $temp = MinAppTemplateMessage::select('template_id','is_open','title')->where('template_id',$corres_id)->where("small_type",1)->where('is_default',1)->first();
        $this->temp_id = empty($temp['template_id']) ? 0 : $temp['template_id'];
        $this->temp_open = empty($temp['is_open']) ? 0 : $temp['is_open'];
        $this->temp_title = empty($temp['title']) ? "" : $temp['title'].'-';
    }

    public function checkDataLength($str,$length)
    {
        if (mb_strlen($str,'utf8') > $length) {
            return mb_substr($str,0,$length,'utf8');
        }

        return $str;
    }
}