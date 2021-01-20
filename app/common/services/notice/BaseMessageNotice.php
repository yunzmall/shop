<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/5/29
 * Time: 15:39
 */

namespace app\common\services\notice;



use app\common\models\notice\MessageTemp;
use app\Jobs\DispatchesJobs;
use app\Jobs\MessageNoticeJob;
use app\Jobs\MiniMessageNoticeJob;

class BaseMessageNotice
{
    protected $template_id=0; //模板ID

    protected $openid=0;  //需要发送消息的会员openid

    protected $openids = [];//需要发送消息的会员openid数组

    protected $data = []; //消息数据

    protected $url='';  //跳转地址

    protected $options=[]; //小程序相关配置

    protected $uniacid=0;

    protected $type=1;  //1-公众号 2-小程序

    public $back = [];   //返回的信息  status (1-有效 0-无效)  message：文字信息

    public function __construct($temp_id,$openid=0,$data,$openids=[],$type,$url="")
    {
        $this->template_id = $temp_id;
        $this->openid = $openid;
        $this->data = $data;
        $this->openids = $openids;
        $this->uniacid = \YunShop::app()->uniacid;
        $this->url = $url;
        $this->type = $type;
        if (app('plugins')->isEnabled('min-app')) {
            $res = \Setting::get('plugin.min_app');
            $this->options['app_id'] = empty($res['key']) ? '' : $res['key'];
            $this->options['secret'] = empty($res['secret']) ? '' : $res['secret'];
        }
    }

    protected function notice($openid)
    {
        if ($this->type == 1 && \Setting::get('shop.notice.toggle') == false) {
            $back['status'] = 0;
            $back['message'] = "后台消息通知未开启";
            return $back;
        }

        if ($this->type == 1) {
            \Log::debug("新版公众号消息-4",$this->template_id);
            \Log::debug("新版公众号消息-5",$openid);
            \Log::debug("新版公众号消息-6",$this->data);
            $job = new MessageNoticeJob($this->template_id, $this->data, $openid, $this->url);
            DispatchesJobs::dispatch($job,DispatchesJobs::LOW);
        }

        if ($this->type == 2) {
            \Log::debug("新版小程序消息-4",$this->template_id);
            \Log::debug("新版小程序消息-5",$openid);
            \Log::debug("新版小程序消息-6",$this->data);
            $job = new MiniMessageNoticeJob($this->options, $this->template_id, $this->data,$openid, $this->url);
            DispatchesJobs::dispatch($job,DispatchesJobs::LOW);
        }

        $back['status'] = 1;
        $back['message'] = "";
        return $back;
    }
}