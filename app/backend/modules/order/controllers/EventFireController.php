<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2019/3/8
 * Time: 11:06 AM
 */

namespace app\backend\modules\order\controllers;


use app\common\components\BaseController;
use app\common\events\order\AfterOrderCreatedEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\models\Order;
use Yunshop\Haifen\listener\OrderListener;

class EventFireController extends BaseController
{
    public $transactionActions = ['*'];
    public function created()
    {

        dump(Order::find(request()->input('id')));
        app()->db->enableQueryLog();
        event(new AfterOrderCreatedEvent(Order::find(request()->input('id'))));
        dd(app()->db->getQueryLog());
        dd(1);

    }

    public function paid()
    {

    }
    public function received()
    {
        event(new AfterOrderReceivedEvent(Order::find(request()->input('id'))));

    }
}