<?php

namespace app\Jobs;

use app\common\facades\EasyWeChat;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageNoticeJob implements  ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    protected $templateId;
    protected $noticeData;
    protected $openId;
    protected $url;
    protected $uniacid;

    /**
     * MessageNoticeJob constructor.
     * @param $templateId
     * @param $noticeData
     * @param $openId
     * @param $url
     * @param $uniacid
     */
    public function __construct($templateId, $noticeData, $openId, $url)
    {
        $this->templateId = $templateId;
        $this->noticeData = $noticeData;
        $this->openId = $openId;
        $this->url = $url;
        $this->uniacid = \YunShop::app()->uniacid;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
		try {
			\YunShop::app()->uniacid = \Setting::$uniqueAccountId =  $this->uniacid;
			if ($this->attempts() > 1) {
				\Log::info('消息通知测试，执行大于两次终止');
				return true;
			}
			$app = EasyWeChat::officialAccount();
			$app->template_message->send([
				'touser' => $this->openId,
				'template_id' => $this->templateId,
				'url' => $this->url,
				'data' => $this->noticeData,
			]);
			return true;
		} catch (\Exception $e) {
			\Log::debug('消息发送失败',$e->getMessage());
		}
    }
}
