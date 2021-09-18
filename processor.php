<?php
/*=============================================================================
#     FileName: processor.php
#         Desc: 
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:08:51
#      History:
=============================================================================*/

if (!defined('IN_IA')) {
    exit('Access Denied');
}
define('IS_API', true);

class Yun_shopModuleProcessor extends WeModuleProcessor
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 预定义的操作, 构造返回文本消息结构
     * @param string $content 回复的消息内容
     * @return array 返回统一响应消息结构
     */
    public function text($content)
    {
        return parent::respText($content);
    }

    /**
     * 预定义的操作, 构造返回转接多客服结构
     * @return array 返回的消息数组结构
     */
    public function custom(array $message = array())
    {
        return parent::respCustom($message);
    }

    /**
     * 预定义的操作, 构造返回图像消息结构
     * @param string $mid 回复的图像资源ID
     * @return array 返回的消息数组结构
     */
    public function image($mid)
    {
        return parent::respImage($mid);
    }

    /**
     * 预定义的操作, 构造返回音乐消息结构
     * @param string $music 回复的音乐定义(包含元素
     *      title - string: 音乐标题,
     *      description - string: 音乐描述,
     *      musicurl - string: 音乐地址,
     *      hqhqmusicurl - string: 高品质音乐地址,
     *      thumb - string: 音乐封面资源ID)
     * @return array 返回的消息数组结构
     */
    public function music(array $music)
    {
        return parent::respMusic($music);
    }

    /**
     * 预定义的操作, 构造返回图文消息结构, 一条图文消息不能超过 10 条内容
     * @param array $news 回复的图文定义,定义为元素集合
     * 	array(
            'title' => '', 		// string: 新闻标题
            'picurl' => '',		// string: 图片链接
            'url' => '',		// string: 原文链接
            'description' => ''	//string: 新闻描述
            );
     * @return array 返回的消息数组结构
     */
    public function news(array $news)
    {
        return parent::respNews($news);
    }

    /**
     * 预定义的操作, 构造返回视频消息结构
     * @param array $video 回复的视频定义(包含两个元素
     *      video - string: 视频资源ID,
     *      thumb - string: 视频缩略图资源ID)
     * @return array 返回的消息数组结构
     */
    public function video(array $video)
    {
        return parent::respVideo($video);
    }

    /**
     * 预定义的操作, 构造返回声音消息结构
     * @param string $mid 回复的音频资源ID
     * @return array 返回的消息数组结构
     */
    public function voice($mid)
    {
        return parent::respVoice($mid);
    }

    public function respond()
    {
//        \Log::debug('------微擎微信公众号消息-----', json_encode($this->rule));

//        $user_data = array(
//            'participation' => json_encode($this),
//            'set_private_send_msg' => $this->message['msgtype'],
//        );
//        pdo_update('yz_forwarding_treasure', $user_data, array('id' => 3));
        //因为任意图片无法匹配到规则 所以图片消息单独处理  start
        if($this->message['msgtype'] == 'image'){
            //因为目前只有转发宝功能需要 所以直接查询转发宝的配置文件  如果存在直接进入事件监听
            $setting = pdo_fetch('select * from ' . tablename('yz_setting') . ' where  `group`=:groupw and `key`=:keyw limit 1', array(
                ':groupw' => 'plugin',
                ':keyw'=>'forwarding-treasure'
            ));

            if(empty($setting)){
                return  false;
            }

//            $setting = serialize($setting['value']);
//            $user_data = array(
//                'area_data' => json_encode($setting),
//            );

//            pdo_update('yz_forwarding_treasure', $user_data, array('id' => 3));
            $plugin = 'forwarding-treasure';


            //引入laravel
            require_once __DIR__.'/bootstrap/autoload.php';
            $app = require_once __DIR__.'/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $kernel->handle(
                $request = \app\framework\Http\Request::capture()
            );


            //微信接口事件
            $response = '';
            event(new \app\common\events\WechatProcessor($this,$plugin,$response));
            return $response;
        }
        //end   以下为以前的代码
        $rule = pdo_fetch('select * from ' . tablename('rule') . ' where id=:id limit 1', array(
            ':id' => $this->rule
        ));
        if (empty($rule)) {
            return false;
        }
        $names  = explode(':', $rule['name']);
        $plugin = isset($names[1]) ? $names[1] : '';
        if (!empty($plugin)) {

            //引入laravel
            require_once __DIR__.'/bootstrap/autoload.php';
            $app = require_once __DIR__.'/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $kernel->handle(
                $request = \app\framework\Http\Request::capture()
            );

            //扫海报事件队列
           /*
            $msg = $this->message;
            $msgType = strtolower($msg['msgtype']);
            $msgEvent = strtolower($msg['event']);

            if ($msgType == 'event' && ($msgEvent == 'scan' || $msgEvent == 'subscribe')) {
                $job = (new \app\Jobs\scanPostConcernQueueJob(YunShop::app()->uniacid, $this))
                    ->delay(\Carbon\Carbon::now()->addMinutes(5));
                dispatch($job);
                \Log::debug('------poster queue job start-----');
            }
           */

            //微信接口事件
            $response = '';
            event(new \app\common\events\WechatProcessor($this,$plugin,$response));
            return $response;

        }
        return false;
    }
}
