<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/1
 * Time: 17:57
 */

namespace app\common\services\notice\applet;


use app\common\models\Member;
use app\common\services\notice\BaseMessageNotice;

class AppletMessageNotice extends BaseMessageNotice
{
    public function __construct($temp_id, $openid = 0, $data, array $openids = [], $type,$url="")
    {
        parent::__construct($temp_id, $openid, $data, $openids, $type,$url);
    }

    public function sendMessage()
    {
        if (empty($this->options['app_id']) || empty($this->options['secret'])) {
            $this->back['status'] = 0;
            $this->back['message'] = "小程序配置错误";
            return $this->back;
        }

        if (empty($this->openid) && count($this->openids) <= 0) {
            $this->back['status'] = 0;
            $this->back['message'] = $this->openid."会员ID为空";
            return $this->back;
        }

        if (empty($this->template_id)) {
            $this->back['status'] = 0;
            $this->back['message'] = "模板ID为空";
            return $this->back;
        }

        if (!empty($this->openid)) {

            $official = $this->notice($this->openid);

            if ($official['status'] != 1) {
                return $official;
            }
        }

        if (!empty($this->openids)) {

            $msg = [];

            foreach ($this->openids as $kk=>$vv) {
                        $rein = $this->notice($vv);

                        if ($rein['status'] != 1) {
                            $msg[] = $rein['message'];
                            continue ;
                        }
            }

            if (count($msg)>0) {
                $this->back['status'] = 0;
                $this->back['message'] = implode(',',$msg);
                return $this->back;
            }
        }

        $this->back['status'] = 1;
        $this->back['message'] = "";
        return $this->back;
    }
}