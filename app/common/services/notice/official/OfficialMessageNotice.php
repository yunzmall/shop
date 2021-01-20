<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/1
 * Time: 17:56
 */

namespace app\common\services\notice\official;


use app\common\models\notice\MessageTemp;
use app\common\services\notice\BaseMessageNotice;

class OfficialMessageNotice extends BaseMessageNotice
{
    public function __construct($temp_id, $openid = 0, $data, array $openids=[], $type,$url="")
    {
        parent::__construct($temp_id, $openid, $data, $openids, $type,$url);
    }

    public function replaceData()
    {
        if (empty($this->template_id) || count($this->data) <= 0) {
            $this->back['status'] = 0;
            $this->back['message'] = "模板ID为空或发送数据为空";
            return ;
        }

        $this->data = MessageTemp::getSendMsg($this->template_id, $this->data);
        $this->template_id = MessageTemp::$template_id;
        if (empty($this->data)) {
            $this->back['status'] = 0;
            $this->back['message'] = "替换后的数据为空";
            return;
        }

        if (empty($this->openid) && count($this->openids) <= 0) {
            $this->back['status'] = 0;
            $this->back['message'] = "会员ID为空";
            return ;
        }

        $this->back['status'] = 1;
        $this->back['message'] = '';
        return ;
    }


    public function sendMessage()
    {

        $this->replaceData();

        if ($this->back['status'] != 1) {
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