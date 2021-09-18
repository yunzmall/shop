<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/26
 * Time: 上午11:32
 */

namespace app\common\models;

use app\common\events\order\OrderPayValidateEvent;
use app\common\traits\HasProcessTrait;
//use app\frontend\models\Member;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\OrderCollection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use app\common\exceptions\AppException;
use app\common\services\PayFactory;
use app\frontend\modules\order\services\OrderService;
use app\frontend\modules\payType\BasePayType;
use app\frontend\modules\payType\CreditPay;
use app\frontend\modules\payType\Remittance;
use app\frontend\modules\payment\managers\OrderPaymentTypeManager;
use Illuminate\Support\Facades\App;

/**
 * Class OrderPay
 * @package app\common\models
 * @property int id
 * @property int uid
 * @property int status
 * @property string pay_sn
 * @property int pay_type_id
 * @property Carbon pay_time
 * @property Carbon refund_time
 * @property float amount
 * @property array order_ids
 * @property Collection orders
 * @property Collection payOrder
 * @property Collection allStatus
 * @property PayType payType
 * @property Member member
 * @property string pay_type_name
 * @property string status_name
 */
class OrderPay extends BaseModel
{
    use HasProcessTrait;

    public $table = 'yz_order_pay';
    protected $guarded = ['id'];
    protected $search_fields = ['pay_sn'];
    protected $casts = ['order_ids' => 'json'];
    protected $dates = ['pay_time', 'refund_time'];
    protected $appends = ['status_name', 'pay_type_name'];
    protected $attributes = [
        'status' => 0,
        'pay_type_id' => 0,
    ];
    const STATUS_UNPAID = 0;
    const STATUS_PAID = 1;
    const STATUS_REFUNDED = 2;

    public static function newVirtual($amount = 0.01)
    {
        $orderPay = new static(['amount' => $amount]);
        $order = new PreOrder(['is_virtual' => 1]);
        $orderPay->setRelation('orders', new OrderCollection([$order]));
        return $orderPay;
    }

    /**
     * 根据paysn查询支付方式
     *
     * @param $pay_sn
     * @return mixed
     */
    public function get_paysn_by_pay_type_id($pay_sn)
    {
        return self::select('pay_type_id')
            ->where('pay_sn', $pay_sn)
            ->value('pay_type_id');
    }

    public function scopeOrderPay(Builder $query)
    {
        return $query->with('payType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'uid');
    }

    /**
     * @return mixed
     */
    public function getStatusNameAttribute()
    {
        return $this->allStatus[$this->status];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllStatusAttribute()
    {
        return collect([
            self::STATUS_UNPAID => '未支付',
            self::STATUS_PAID => '已支付',
            self::STATUS_REFUNDED => '已退款',
        ]);
    }

    /**
     * @return string
     */
    public function getPayTypeNameAttribute()
    {
        return $this->payType->name;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, (new OrderPayOrder)->getTable(), 'order_pay_id', 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payType()
    {
        return $this->belongsTo(PayType::class);
    }

    /**
     * @return \Illuminate\Support\Collection|static
     */
    public function getPaymentTypes()
    {
        /**
         * @var OrderPaymentTypeManager $orderPaymentTypeManager
         */
        $orderPaymentTypeManager = app('PaymentManager')->make('OrderPaymentTypeManager');
        $paymentTypes = $orderPaymentTypeManager->getOrderPaymentTypes($this);
        return $paymentTypes;
    }

    /**
     * @return \Illuminate\Support\Collection|static
     */
    public function getAllPaymentTypes()
    {
        /**
         * @var OrderPaymentTypeManager $orderPaymentTypeManager
         */
        $orderPaymentTypeManager = app('PaymentManager')->make('OrderPaymentTypeManager');
        $paymentTypes = $orderPaymentTypeManager->getAllOrderPaymentTypes($this);
        return $paymentTypes;
    }


    /**
     * 支付
     * @param int $payTypeId
     * @throws AppException
     */
    public function pay($payTypeId = null)
    {
        if (!is_null($payTypeId)) {
            $this->pay_type_id = $payTypeId;
        }
        $this->payValidate();

        $this->status = self::STATUS_PAID;
        $this->pay_time = time();
        $this->save();

        $this->orders->each(function ($order) {
            OrderService::orderPay(['order_id' => $order->id, 'order_pay_id' => $this->id, 'pay_type_id' => $this->pay_type_id]);
        });
    }

    public function applyValidate()
    {
        // 校验库存
    }

    /**
     * 支付校验
     * @throws AppException
     */
    private function payValidate()
    {
        if (is_null($this->pay_type_id)) {
            throw new AppException('请选择支付方式');
        }
//        if ($this->status > self::STATUS_UNPAID) {
//            throw new AppException('(ID' . $this->id . '),此流水号已支付');
//        }

        if ($this->orders->isEmpty()) {
            throw new AppException('(ID:' . $this->id . ')未找到对应订单');
        }

        $this->orders->each(function (\app\common\models\Order $order) {
        	if ($order->status > Order::WAIT_PAY) {
                throw new AppException('(ID:' . $order->id . ')订单已付款,请勿重复付款');
            }
            if ($order->status == Order::CLOSE) {
                throw new AppException('(ID:' . $order->id . ')订单已关闭,无法付款');
            }
        });
        if (bccomp($this->orders->sum('price'), $this->amount) != 0) {
            throw new AppException('(ID' . $this->id . '),此流水号对应订单价格发生变化,请重新请求支付');
        };
    }

	/**
	 * 支付事件校验，点击支付按钮时触发
	 */
    private function OrderPayValidate()
	{
		$this->orders->each(function (\app\common\models\Order $order) {
			event(new OrderPayValidateEvent($order));
		});
	}

    /**
     * @throws AppException
     */
    public function applyPay()
    {
        return $this->getPayType()->applyPay();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payOrder()
    {
        return $this->hasMany(PayOrder::class, 'out_order_no', 'pay_sn');
    }

	/**
	 * 代付记录
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
    public function behalfPay()
	{
		return $this->hasOne(OrderBehalfPayRecord::class,'order_pay_id','id');
	}

    /**
     * 获取支付参数
     * @param int $payTypeId
     * @param array $payParams
     * @return array
     * @throws AppException
     */
    public function getPayResult($payTypeId = null, $payParams = [])
    {

        if ($this->created_at->timestamp + 60 < time()) {
            throw new AppException('支付请求记录已过期,请返回订单页面重新选择付款');
        }

        if (!is_null($payTypeId)) {
            $this->pay_type_id = $payTypeId;
        }
        $this->payValidate();
		// 支付前校验事件
		$this->OrderPayValidate();
        // 从丁哥的接口获取统一的支付参数

        $query_str = $this->getPayType()->getPayParams($payParams);

        $pay = PayFactory::create($this->pay_type_id);
        $result = $pay->doPay($query_str, $this->pay_type_id);
        if (!isset($result)) {
            throw new AppException('获取支付参数失败');
        }
        return $result;
    }

    /**
     * 获取支付类型对象
     * @return PayType|BasePayType
     * @throws AppException
     */
    private function getPayType()
    {
        if (!$this->payType instanceof BasePayType) {
            if ($this->pay_type_id == PayType::CREDIT) {
                $payType = CreditPay::find($this->pay_type_id);
            } elseif ($this->pay_type_id == PayType::REMITTANCE) {
                $payType = Remittance::find($this->pay_type_id);

            } else {
                $payType = BasePayType::find($this->pay_type_id);
            }

            if (!isset($payType)) {
                throw new AppException("未找到对应支付方式(id:{$this->pay_type_id})");
            }
            /**
             * @var BasePayType $payType
             */
            $payType->setOrderPay($this);
            $this->setRelation('payType', $payType);
        }
        return $this->payType;
    }

    /**
     * 快速退款
     * @throws AppException
     */
    public function fastRefund(Order $order = null)
    {
        //订单退款金额
        if (!isset($order)) {
            $amount = $this->amount;
        } else {
            event(new \app\common\events\order\BeforeOrderRefundedEvent($order));
            $amount = $order->price;
        }

        $pay = PayFactory::create($this->pay_type_id);

        $totalmoney = $this->amount; //订单总金额

        try {
            $result = $pay->doRefund($this->pay_sn, $totalmoney, $amount);

//            if ($result) {
//                $this->status = OrderPay::STATUS_REFUNDED;
//                $this->refund_time = time();
//                $this->save();
//            }

            return $result;
        } catch (\Exception $e) {
            \Log::debug('错误支付回调参数',$e->getMessage());
            throw new AppException($e->getMessage());
        }
    }
    public function refund(){
        $this->status = OrderPay::STATUS_REFUNDED;
        $this->save();
    }
    /**
     * 快速退款(退回余额)
     * @throws AppException
     */
    public function fastRefund2(Order $order = null)
    {
        //订单退款金额
        if (!isset($order)) {
            $this->status = OrderPay::STATUS_REFUNDED;
            $amount = $this->amount;
        } else {
            $amount = $order->price;
        }

        $pay = PayFactory::create(3);

        $totalmoney = $this->amount; //订单总金额

        try {
            $result = $pay->doRefund($this->pay_sn, $totalmoney, $amount);

            if ($result) {
                $this->save();
            }

            return $result;
        } catch (\Exception $e) {
            throw new AppException($e->getMessage());
        }
    }

    public function save(array $options = [])
    {
        //如果修改之前不是退款状态，并且修改之后是退款状态，则更新退款时间
        if ($this->getOriginal('status') != self::STATUS_REFUNDED and $this->status == self::STATUS_REFUNDED) {
            $this->refund_time = time();
        }
        return parent::save($options); // TODO: Change the autogenerated stub
    }
}