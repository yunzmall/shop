<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/6/5
 * Time: 下午7:53
 */

namespace app\frontend\modules\order\services;


use app\common\services\notice\applet\buyer\OrderBuyerPackageSendMinNotice;
use app\common\services\notice\applet\buyer\OrderBuyerPayedMinNotice;
use app\common\services\notice\applet\buyer\OrderBuyerReceivedMinNotice;
use app\common\services\notice\applet\buyer\OrderBuyerSendMinNotice;
use app\common\services\notice\applet\OrderPayedMinNotice;
use app\common\services\notice\applet\OrderReceivedMinNotice;
use app\common\services\notice\official\buyer\OrderBuyerCancelNotice;
use app\common\services\notice\official\buyer\OrderBuyerCreateNotice;
use app\common\services\notice\official\buyer\OrderBuyerPackageSendNotice;
use app\common\services\notice\official\buyer\OrderBuyerPayedNotice;
use app\common\services\notice\official\buyer\OrderBuyerReceivedNotice;
use app\common\services\notice\official\buyer\OrderBuyerSendNotice;
use app\common\services\notice\official\GoodsBuyNotice;
use app\common\services\notice\official\OrderCreateNotice;
use app\common\services\notice\official\OrderPayedNotice;
use app\common\services\notice\official\OrderReceivedNotice;
use app\frontend\modules\order\services\message\BuyerMessage;
use app\frontend\modules\order\services\message\ShopMessage;

class MessageService extends \app\common\services\MessageService
{
    private $buyerMessage;
    private $shopMessage;
    protected $formId;
    protected $noticeType;
    private $orderModel ;
    function __construct($order,$formId = '',$type = 1,$title='')
    {
        $this->buyerMessage = new BuyerMessage($order,$formId,$type,$title);
        $this->shopMessage = new ShopMessage($order,$formId,$type,$title);
        $this->formId = $formId;
        $this->noticeType = $type;
        $this->orderModel = $order;
    }

    public function canceled()
    {
       // $this->buyerMessage->canceled();
        $buyerNotice = new OrderBuyerCancelNotice($this->orderModel);
        $buyerNotice->sendMessage();
    }

    public function created()
    {
       // $this->shopMessage->goodsBuy(1);
       // $this->buyerMessage->created();
       // $this->shopMessage->created();

        //客户
        $buyerNotice = new OrderBuyerCreateNotice($this->orderModel);
        $buyerNotice->sendMessage();

        //管理员
        $managerNotice = new OrderCreateNotice($this->orderModel, 1);
        $managerNotice->sendMessage();

        //商品
        $goodsNotice = new GoodsBuyNotice($this->orderModel,1);
        $goodsNotice->sendMessage();
    }

    public function paid()
    {
       // $this->shopMessage->goodsBuy(2);
      //  $this->buyerMessage->paid();
      //  $this->shopMessage->paid();

        //小程序消息通知
        $buyerNotice = new OrderBuyerPayedMinNotice($this->orderModel);
        $buyerNotice->sendMessage();
        $sendNotice = new OrderPayedMinNotice($this->orderModel);
        $sendNotice->sendMessage();

        //公众号消息通知
        $buyerOfficialNotice = new OrderBuyerPayedNotice($this->orderModel);
        $buyerOfficialNotice->sendMessage();

        $officialNotice = new OrderPayedNotice($this->orderModel, 2);
        $officialNotice->sendMessage();

        //商品
        $goodsNotice = new GoodsBuyNotice($this->orderModel,2);
        $goodsNotice->sendMessage();
    }

    public function sent()
    {
      //  $this->buyerMessage->sent();
        $sendBuyerNotice = new OrderBuyerSendMinNotice($this->orderModel);
        $sendBuyerNotice->sendMessage();

        $sendOfficialNotice = new OrderBuyerSendNotice($this->orderModel);
        $sendOfficialNotice->sendMessage();

    }

    public function packageSent()
    {
        $sendBuyerNotice = new OrderBuyerPackageSendMinNotice($this->orderModel);
        $sendBuyerNotice->sendMessage();

        $sendOfficialNotice = new OrderBuyerPackageSendNotice($this->orderModel);
        $sendOfficialNotice->sendMessage();
    }



    public function received()
    {
       // $this->shopMessage->goodsBuy(3);
      //  $this->shopMessage->received();
       // $this->buyerMessage->received();

        //小程序
        $receiveBuyerNotice = new OrderBuyerReceivedMinNotice($this->orderModel);
        $receiveBuyerNotice->sendMessage();

        $receiveNotice = new OrderReceivedMinNotice($this->orderModel);
        $receiveNotice->sendMessage();

        //公众号
        $receiveBuyerOfficialNotice = new OrderBuyerReceivedNotice($this->orderModel);
        $receiveBuyerOfficialNotice->sendMessage();

        $receiveOfficailNotice = new OrderReceivedNotice($this->orderModel, 3);
        $receiveOfficailNotice->sendMessage();

        //商品
        $goodsNotice = new GoodsBuyNotice($this->orderModel,3);
        $goodsNotice->sendMessage();

    }
}