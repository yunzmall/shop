<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/2
 * Time: 上午11:24
 */

namespace app\common\models;

use app\common\events\order\HiddenOrderCommentEvent;
use app\common\exceptions\AppException;
use app\common\models\comment\CommentConfig;
use app\common\models\goods\GoodsDispatch;
use app\common\models\order\OrderGoodsChangePriceLog;
use app\common\models\orderGoods\OrderGoodsDeduction;
use app\common\models\orderGoods\OrderGoodsDiscount;
use app\common\models\orderGoods\OrderGoodsExpansion;
use app\common\models\refund\RefundApply;
use app\common\models\refund\RefundGoodsLog;
use app\frontend\modules\orderGoods\stock\GoodsStock;
use app\framework\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Yunshop\Merchant\common\models\MerchantGoods;

/**
 * Class OrderGoods
 * @package app\common\models
 * @property int comment_status
 * @property int total
 * @property int goods_id
 * @property int is_refund
 * @property int goods_option_id
 * @property Goods goods
 * @property GoodsOption goodsOption
 * @property Collection orderGoodsDeductions
 * @property Collection manyRefundedGoodsLog
 * @method static self byIsRefund()
 */
class OrderGoods extends BaseModel
{
    public $table = 'yz_order_goods';
    protected $hidden = ['order_id','manyRefundedGoodsLog','hasOneRefund'];
    protected $appends = ['buttons','after_sales'];
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $attributes = [
        'goods_option_id' => 0,
        'goods_option_title' => '',
        'comment_status' => 0
    ];
    protected $search_fields = ['title'];


    const CAN_REFUND = 0;//未退过款
    const FIRST_REFUND = 1; //首次退款
//    const MANY_REFUND = 2; //重复退款
//    const FINAL_REFUND = 3;//最终

    /**
     * 获取支付商品名称
     * @return string
     */
    public function getPayTitleAttribute()
    {
        return $this->goods->alias?: $this->title;
    }


    /**
     * 订单商品售后信息显示数组
     * @return array
     */
    public function getAfterSalesAttribute()
    {

        $after_sales = [
            'refund_id' => $this->refund_id?:0,
            'refund_type_name' => '',
            'refund_status_name' => '已退款',
            'refunded_total' => '',
            'complete_quantity' => $this->getRefundTotal(), //已退款完成数量
        ];

        if ($this->refund_id) {
            $after_sales['refund_type_name'] = $this->hasOneRefund->refund_type_name;
            $after_sales['refund_status_name'] = $this->hasOneRefund->status_name;
            $after_sales['refunded_total'] =  $this->manyRefundedGoodsLog
                ->where('refund_id', $this->refund_id)
                ->sum('refund_total');

            $after_sales['complete_quantity'] =  $after_sales['complete_quantity'] - $after_sales['refunded_total'];
        } else {
            //过滤换货售后
            $after_sales['refunded_total'] = $after_sales['complete_quantity'];
        }


        return $after_sales;
    }

    public function getRefundTotal()
    {
        return $this->manyRefundedGoodsLog
            ->where('refund_type','!=', RefundApply::REFUND_TYPE_EXCHANGE_GOODS)
//            ->where('status','!=', RefundGoodsLog::CANCEL_STATUS)
            ->sum('refund_total');
    }

    /**
     * 前端商品评论按钮
     * @return array
     */
    public function getButtonsAttribute()
    {
        if($this->order->uid != \YunShop::app()->getMemberId()){
            return [];
        }
        $result = [];
        $config = CommentConfig::getSetConfig();

        if ($this->order->status != 3) {
            return $result;
        }
        event($event = new HiddenOrderCommentEvent($this));
        //开启评价
        if ($config->is_order_comment_entrance && $event->isShow()) {
            if ($this->comment_status == 0) {
                $result[] = [
                    'name' => '评价',
                    'api' => '',//goods.comment.create-comment-page
                    'value' => '0'
                ];
            } elseif ($this->comment_status == 1) {
                $result[] = [
                    'name' => '查看评价',
                    'api' => '',
                    'value' => '2'
                ];

                //开启追评
                if ($config->is_additional_comment) {
                    $result[] = [
                        'name' => '追评',
                        'api' => '',
                        'value' => '1'
                    ];
                }
            } else {
                $result[] = [
                    'name' => '查看评价',
                    'api' => '',
                    'value' => '2'
                ];
            }
        }

        return $result;
    }

    public function orderGoodsDeductions()
    {
        return $this->hasMany(OrderGoodsDeduction::class,'order_goods_id');
    }
    public function orderGoodsDiscounts()
    {
        return $this->hasMany(OrderGoodsDiscount::class,'order_goods_id');
    }

    public function hasOneGoods()
    {
        return $this->hasOne($this->getNearestModel('Goods'), 'id', 'goods_id');
    }

    public function hasOneOrder()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function hasOneMerchantGoods()
    {
        return $this->hasOne(MerchantGoods::class, 'goods_id', 'goods_id');
    }

    //商品
    public function goods()
    {
        //todo blank 如果订单未支付时删除了商品，再支付会报错，原因是修改商品库存时关联商品模型为null,解决方式如下：
//        return $this->belongsTo(Goods::class)->withTrashed();
        return $this->belongsTo(Goods::class);
    }

    public function belongsToGood()
    {
        return $this->belongsTo(self::getNearestModel('Goods'), 'goods_id', 'id');
    }

    //商品规格
    public function goodsOption()
    {
        return $this->hasOne(app('GoodsManager')->make('GoodsOption'), 'id', 'goods_option_id');
    }


    public function hasOneGoodsDispatch()
    {
        return $this->hasOne(GoodsDispatch::class, 'goods_id', 'goods_id');
    }


    public function hasOneComment()
    {
        return $this->hasOne(\app\frontend\modules\goods\models\Comment::class, 'id', 'comment_id');
    }

    public function orderGoodsChangePriceLogs()
    {
        return $this->hasMany(OrderGoodsChangePriceLog::class, 'order_id', 'id');

    }

    public function hasOneRefund()
    {
        return $this->hasOne(\app\common\models\refund\RefundApply::class, 'id', 'refund_id');
    }


    /**
     * 已售后订单商品记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manyRefundedGoodsLog()
    {
        return $this->hasMany(RefundGoodsLog::class, 'order_goods_id', 'id');
    }

    public function scopeOrderGoods(Builder $query)
    {
        return $query->select([
            'id', 'order_id', 'goods_id', 'refund_id','is_refund','goods_price', 'total', 'goods_option_title',
            'price', 'goods_market_price', 'goods_cost_price', 'thumb', 'title', 'goods_sn',
            'payment_amount','deduction_amount','vip_price','product_sn','type'
        ])->with(['goods'=>function ($query) {
            //在这里添加'weight','product_sn','goods_sn'字段，因为快递打印那边要用
            return $query->select(['id','title','status','type','thumb','sku','market_price','price','cost_price','weight','product_sn','goods_sn','alias']);
        }]);
    }

    //已退过款商品
    public function scopeByIsRefund(Builder $query)
    {
        return $query->where('is_refund', '>', OrderGoods::CAN_REFUND);
    }

    /**
     * 订单商品是否已退过款
     * @return mixed
     */
    public function isRefund()
    {
        return $this->is_refund > self::CAN_REFUND;
    }


    /**
     * 商品已退款次数
     */
    public function refundNumber()
    {
        return  $this->is_refund;
    }



    public function isOption()
    {
        return !empty($this->goods_option_id);
    }

    /**
     * @throws AppException
     */
    public function stockEnough()
    {
        if($this->isOption()){
            // 规格
            if (!$this->goodsOption->stockEnough($this->total)) {
                throw new AppException('(ID:' . $this->goods_id . ')商品库存不足');
            }
        }else{
            // 普通商品
            if (!$this->goods->stockEnough($this->total)) {
                throw new AppException('(ID:' . $this->goods_id . ')商品库存不足');
            }
        }

    }

    public function expansion()
    {
        return $this->hasMany(OrderGoodsExpansion::class);
    }

    public function getExpansion($key = '')
    {
        if (!$key) {
            return $this->expansion;
        }

        return isset($this->expansion->where('key', $key)->first()['value']) ? $this->expansion->where('key', $key)->first()['value'] : null;

    }

    /**
     * 订单商品是否包邮
     * @return bool true 包邮
     */
    public function isFreeShipping()
    {

        if (isset($this->goods->hasOneSale) && $this->goods->hasOneSale->isFree($this)) {
            return true;
        }

        return false;
    }

    private $goodsStock;

    public function goodsStock()
    {
        if (!isset($this->goodsStock)) {
            $this->goodsStock = new GoodsStock($this);
        }
        return $this->goodsStock;
    }

}