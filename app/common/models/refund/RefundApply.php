<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/12
 * Time: 下午1:38
 */

namespace app\common\models\refund;

use app\common\models\BaseModel;
use app\common\models\Member;
use app\common\models\Order;
use app\frontend\modules\refund\services\RefundService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RefundApply
 * @package app\common\models\refund
 * @property float price 退款金额
 * @property float freight_price 退款运费
 * @property float other_price 退款其他金额
 * @property float apply_price 退款商品申请金额和
 * @property int status 售后状态
 * @property int part_refund  0、换货售后无状态；1、部分退款；2、最后一次退款；3、一次性全部申请退款；4、订单关闭并退款
 * @property integer refund_type 售后类型
 * @property Order order
 * @property ReturnExpress returnExpress
 * @property Collection refundOrderGoods
 * @property Collection processLog
 * @property Collection hasManyResendExpress
 *
 */
class RefundApply extends BaseModel
{
    protected $table = 'yz_order_refund';
    protected $hidden = ['updated_at', 'created_at','uniacid', 'uid', 'order_id'];
    protected $fillable = [];
    protected $guarded = ['id'];
    static protected $needLog = true;
    protected $appends = [
        'refund_type_name', 'status_name', 'is_refunded', 'is_refunding', 'is_refund_fail', 'plugin_id',
        'receive_status_name', 'refund_way_type_name',
    ];
    protected $attributes = [
        'images' => '[]',
        'refund_proof_imgs' => '[]',
        'content' => '',
        'reply' => '',
        'remark' => '',
        'refund_address' => '',
        'reject_reason' => '',
        'refund_way_type' => 0,
        'part_refund' => 0,
        'receive_status' => 0,
        'apply_price' => 0,
        'freight_price' => 0,
        'other_price' => 0,
    ];
    protected $casts = [
        'images' => 'json',
        'refund_proof_imgs' => 'json'
    ];

    //类型
    const REFUND_TYPE_REFUND_MONEY = 0;
    const REFUND_TYPE_RETURN_GOODS = 1;
    const REFUND_TYPE_EXCHANGE_GOODS = 2;


    //状态
    const CLOSE = '-3';//关闭  废弃换货也是售后完成 状态应该是 6
    const CANCEL = '-2';//用户取消
    const REJECT = '-1';//驳回
    const WAIT_CHECK = '0';//待审核
    const WAIT_RETURN_GOODS = '1';//待退货
    const WAIT_RECEIVE_RETURN_GOODS = '2';//待收货\用户发货
    const WAIT_RESEND_GOODS = '3';//重新发货
    const WAIT_RECEIVE_RESEND_GOODS = '4';//重新收货\商家发货
    const WAIT_REFUND = '5';//待打款
    const COMPLETE = '6';//已完成
    const CONSENSUS = '7';//手动退款


    //区别
    const PART_REFUND = 1;
    const ORDER_CLOSE = 4;


    public function getDates()
    {
        return ['create_time', 'refund_time', 'operate_time', 'send_time', 'return_time', 'end_time', 'cancel_pay_time', 'cancel_send_time'] + parent::getDates();
    }


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->uniacid)) {
            $this->uniacid = \YunShop::app()->uniacid;
        }

        // todo 转移到前端
//        if (!isset($this->uid)) {
//            $this->uid = \YunShop::app()->getMemberId();
//        }
    }

    //禁止使用该方法，使用  RefundOperationService::orderCloseAndRefund($order) 创建售后申请
    public static function createByOrder(Order $order)
    {

        $refundApply = new  \app\backend\modules\refund\services\operation\OrderCloseAndRefund();
        $refundApply->setRelation('order',$order);
        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $refundApply;


        $refundApply = new static();
        $refundApply->images = [];
        $refundApply->content = '订单状态改变失败退款';
        $refundApply->reason = '订单状态改变失败退款';
        $refundApply->order_id = $order->id;
        $refundApply->refund_type = 0;
        $refundApply->refund_sn = RefundService::createOrderRN();
        $refundApply->create_time = time();
        $refundApply->price = $order->price;
        return $refundApply;
    }


    /**
     * 获取订单已售后完成记录
     * @param $order_id
     * @param array $typeArray 售后类型 默认只查询： 退款、退货退款的
     * @return self
     */
    public static function getAfterSales($order_id, $typeArray = [0,1])
    {
        $model = self::select([
            'order_id', 'uid','refund_type', 'status', 'refund_sn', 'part_refund',
            'price', 'apply_price', 'freight_price', 'other_price', 'price',
            ])->where('status', '>=', RefundApply::COMPLETE)
            ->where('order_id', $order_id);

        if ($typeArray) {
            $model->whereIn('refund_type',  $typeArray);
        }

        return $model;
    }


    /**
     * 商城会员信息
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneMember()
    {
        return $this->hasOne(Member::class,'uid', 'uid');
    }


    public function returnExpress()
    {
        return $this->hasOne(ReturnExpress::class, 'refund_id', 'id');
    }

    public function resendExpress()
    {
        return $this->hasOne(ResendExpress::class, 'refund_id', 'id')->orderBy('id','desc');
    }

    public function changeLog()
    {
        return $this->hasOne(RefundChangeLog::class, 'refund_id', 'id');
    }

    public function processLog()
    {
        return $this->hasMany(RefundProcessLog::class, 'refund_id', 'id')->orderBy('id','desc');
    }

    public function hasManyResendExpress()
    {
        return $this->hasMany(ResendExpress::class, 'refund_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refundOrderGoods()
    {
        //这里需要查询软删除的，显示需要
        return $this->hasMany(RefundGoodsLog::class, 'refund_id', 'id')->withTrashed();
    }



    public function getAllTypeAttribute()
    {
        return collect([
            [
                'id' => self::REFUND_TYPE_REFUND_MONEY,
                'name' => '仅退款',
            ], [
                'id' => self::REFUND_TYPE_RETURN_GOODS,
                'name' => '退款退货',
            ], [
                'id' => self::REFUND_TYPE_EXCHANGE_GOODS,
                'name' => '换货',
            ],
        ]);
    }

    public function getAllStatusAttribute()
    {
        return collect([
            [
                'id' => self::CLOSE,
                'name' => '已关闭',
            ], [
                'id' => self::CANCEL,
                'name' => '用户取消',
            ], [
                'id' => self::REJECT,
                'name' => '驳回',
            ], [
                'id' => self::WAIT_CHECK,
                'name' => '待审核',
            ], [
                'id' => self::WAIT_RETURN_GOODS,
                'name' => '待退货',
            ], [
                'id' => self::WAIT_RECEIVE_RETURN_GOODS,
                'name' => '待收货',
            ], [
                'id' => self::WAIT_RESEND_GOODS,
                'name' => '重新发货',
            ], [
                'id' => self::WAIT_RECEIVE_RESEND_GOODS,
                'name' => '重新收货',
            ], [
                'id' => self::COMPLETE,
                'name' => '已完成',
            ], [
                'id' => self::CONSENSUS,
                'name' => '手动退款',
            ]
        ]);
    }


    public function getRefundTypeNameAttribute()
    {
        return $this->getRefundTypeName()[$this->refund_type];
    }

    protected function getRefundTypeName()
    {
        return [
            self::REFUND_TYPE_REFUND_MONEY => '退款',
            self::REFUND_TYPE_RETURN_GOODS => '退款退货',
            self::REFUND_TYPE_EXCHANGE_GOODS => '换货',
        ];
    }

    protected function getStatusNameMapping()
    {
        return [
            self::CLOSE => '已关闭',
            self::CANCEL => '用户取消',
            self::REJECT => '已驳回',
            self::WAIT_CHECK => '待审核',
            self::WAIT_RETURN_GOODS => '待退货',
            self::WAIT_RECEIVE_RETURN_GOODS => '商家待收货',
            self::WAIT_RESEND_GOODS => '商家分批发货',
            self::WAIT_RECEIVE_RESEND_GOODS => '待买家收货',
            self::WAIT_REFUND => '待退款',
            self::COMPLETE => '已' . $this->getRefundTypeName()[$this->refund_type],
            self::CONSENSUS => '已手动退款',
        ];

    }

    public function scopeRefunding($query)
    {
        return $query->where('status', '>=', self::WAIT_CHECK)->where('status', '<', self::COMPLETE);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', '>=', self::COMPLETE);
    }

    public function scopeRefundMoney($query)
    {
        return $query->where('refund_type', self::REFUND_TYPE_REFUND_MONEY);
    }

    public function scopeReturnGoods($query)
    {
        return $query->where('refund_type', self::REFUND_TYPE_RETURN_GOODS);
    }

    public function scopeExchangeGoods($query)
    {
        return $query->where('refund_type', self::REFUND_TYPE_EXCHANGE_GOODS);
    }

    public function getIsPlugin($order_id)
    {
        return \app\common\models\Order::where('id', $order_id)->select('is_plugin', 'plugin_id')->first();
    }

    public function getSupplierId($order_id)
    {
        return \Yunshop\Supplier\common\models\SupplierOrder::where('order_id', $order_id)->value('supplier_id');
    }

    public function getStoreId($order_id)
    {
        return \Yunshop\StoreCashier\common\models\StoreOrder::where('order_id', $order_id)->value('store_id');
    }

    public function getStatusNameAttribute()
    {

        return $this->getStatusNameMapping()[$this->status];
    }

    public function getReceiveStatusNameAttribute()
    {
        switch ($this->receive_status) {
            case 0:
                return '未收到货';
            case 1:
                return '已收到货';
            default:
                return '不填写';
        }
    }

    public function getRefundWayTypeNameAttribute()
    {
        switch ($this->refund_way_type) {
            case 0:
                return '自行寄回';
            case 1:
                return '上门取货';
            default:
                return '其他方式';
        }
    }

    public function getIsRefundedAttribute()
    {
        return $this->isRefunded();
    }

    public function getIsRefundingAttribute()
    {
        return $this->isRefunding();
    }

    public function getIsRefundFailAttribute()
    {
        return $this->isRefundFail();
    }


    public function getPartRefundNameAttribute()
    {
        switch ($this->part_refund) {
            case 0:
                return '售后';
            case 1:
                return '部分退款';
            case 2:
                return '最后退款';
            case 3:
                return '全部退款';
            case 4:
                return '订单关闭并退款';
            default:
                return $this->part_refund;
        }
    }


    /**
     * 是否部分退款
     * @return bool
     */
    final public function isPartRefund()
    {
        return $this->part_refund === self::PART_REFUND;
    }

    /**
     * 退款失败
     * @return bool
     */
    public function isRefundFail()
    {
        if ($this->status < self::WAIT_CHECK) {
            return true;
        }
        return false;
    }

    /**
     * 已退款
     * @return bool
     */
    public function isRefunded()
    {
        if ($this->status >= self::COMPLETE) {
            return true;
        }
        return false;
    }

    /**
     * 退款中
     * @return bool
     */
    public function isRefunding()
    {
        if ($this->status < self::WAIT_CHECK) {
            return false;
        }
        if ($this->status >= self::COMPLETE) {
            return false;
        }
        return true;
    }

    //用于区分插件与商城订单
    public function getPluginIdAttribute()
    {
        if ($this->order) {
            return $this->order->plugin_id;
        }

        return 0;
    }

    /**
     * todo 为了配合供应商做出的修改,需要重新考虑区分插件与商城订单的机制
     * {@inheritdoc}
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function (Builder $builder) {
            $builder->uniacid();
        });
    }
    public function save(array $options = [])
    {
//        if(!in_array($this->getOriginal('status'),[6,7]) && $this->isRefunded()){
//            $this->order->hasOneOrderPay->refund();
//        }
        return parent::save($options);
    }
}