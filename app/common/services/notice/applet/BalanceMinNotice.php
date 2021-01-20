<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/8
 * Time: 9:06
 */

namespace app\common\services\notice\applet;

use app\common\services\credit\ConstService;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;

class BalanceMinNotice extends BaseMessageBody
{

    protected $nickName;
    protected $now;
    protected $change;
    protected $type;
    protected $memberModel;

    public function __construct($nickName,$now,$change,$type,$memberModel)
    {
        $this->nickName = $nickName;
        $this->now = $now;
        $this->change = $change;
        $this->type = $type;
        $this->memberModel = $memberModel;
    }

    use MiniNoticeTemplate;

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'keyword1' => ['value' => $this->nickName],// 会员昵称
            'keyword2' => ['value' => date('Y-m-d H:i', time())],//变动时间
            'keyword3' => ['value' => $this->change],// 变动金额
            'keyword4' => ['value' => $this->now],//  当前余额
            'keyword5' => ['value' => $this->getType()],// 变动类型
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate("账户余额提醒");

        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
        }

        $this->organizeData();
        $result = (new AppletMessageNotice($this->temp_id,$this->memberModel->hasOneMiniApp->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }

    private function getType()
    {
       return (new ConstService(''))->sourceComment()[$this->type];
    }
}