<?php

namespace app\backend\modules\refund\models;

use Illuminate\Http\Request;
use app\backend\modules\order\models\Order;
use app\backend\modules\refund\models\type\RefundMoney;
use app\backend\modules\refund\models\type\ExchangeGoods;
use app\backend\modules\refund\models\type\ReturnGoods;
use app\common\exceptions\AdminException;

/**
 * Class RefundApply
 * @package app\backend\modules\refund\models
 * @property Order order
 */
class RefundApply extends \app\common\models\refund\RefundApply
{
    static protected $needLog = true;

    protected $typeInstance;

    protected $request;

    //确认退款
    public function refundMoney()
    {
        return $this->getTypeInstance()->refundMoney();
    }

    //驳回
    public function reject()
    {
        return $this->getTypeInstance()->reject();
    }

    //同意申请
    public function pass()
    {
        return $this->getTypeInstance()->pass();
    }


    //手动退款
    public function consensus()
    {
        return $this->getTypeInstance()->consensus();
    }

    //没用
    public function receiveReturnGoods()
    {
        //todo 补充当退款类型实例请求 收货请求时的提示
        return $this->getTypeInstance()->receiveReturnGoods();
    }

    //换货完成
    public function close()
    {
        return $this->getTypeInstance()->close();

    }

    //商家发货
    public function resend()
    {
        return $this->getTypeInstance()->resend();
    }

    /**
     * @return ExchangeGoods|RefundMoney|ReturnGoods
     * @throws AdminException
     */
    protected function getTypeInstance()
    {
        if (!isset($this->typeInstance)) {
            switch ($this->refund_type) {
                case self::REFUND_TYPE_REFUND_MONEY:
                    $this->typeInstance = new RefundMoney($this);
                    break;
                case self::REFUND_TYPE_RETURN_GOODS:
                    $this->typeInstance = new ReturnGoods($this);
                    break;
                case self::REFUND_TYPE_EXCHANGE_GOODS:
                    $this->typeInstance = new ExchangeGoods($this);
                    break;
                default:
                    throw new AdminException('退款类型不存在');
                    break;
            }
        }

        return $this->typeInstance;

    }

    /**
     * @param $request
     */
    public function setRequest($request)
    {
        if ($request instanceof Request) {
            $this->request = $request;
        }
        if (is_array($request)) {
            $this->request = request()->merge($request);
        }
    }

    public function getRequest()
    {
        if (!isset($this->request)) {
            $this->request = request();
        }
        return $this->request;
    }
}