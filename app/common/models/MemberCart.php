<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/2
 * Time: 下午4:47
 */

namespace app\common\models;

use app\common\exceptions\AppException;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberCart
 * @package app\common\models
 * @property int plugin_id
 * @property int option_id
 * @property int total
 * @property int member_id
 * @property int goods_id
 * @property Goods goods
 * @property GoodsOption goodsOption
 * @property Member member
 */
class MemberCart extends BaseModel
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $table = 'yz_member_cart';

    public function isOption()
    {
        return !empty($this->option_id);
    }

    public function goodsOption()
    {
        return $this->belongsTo(app('GoodsManager')->make('GoodsOption'), 'option_id');
    }

    public function goods()
    {
        return $this->belongsTo(app('GoodsManager')->make('Goods'));
    }

    /**
     * 购物车验证
     * @throws AppException
     */
    public function validate()
    {
        if (!isset($this->goods)) {
            throw new AppException('(ID:' . $this->goods_id . ')未找到商品或已经删除');
        }


        //todo 验证商品是否启用规格
        $this->goods->verifyOption($this->option_id);

        //商品基本验证
        $this->goods->generalValidate($this->member, $this->total);


        //商品购买权限验证
        if (isset($this->goods->hasOnePrivilege)) {

            //开启按规格限制购买
            if ( $this->goods->hasOnePrivilege->option_id_array && $this->isOption()) {
                $privilegeValidate = new \app\common\services\goods\GoodsOptionBuyLimit($this->goods->hasOnePrivilege, $this->goodsOption);

                $privilegeValidate->goodsValidate($this->member, $this->total);
            } else {
                $this->goods->hasOnePrivilege->validate($this->member, $this->total);
            }
        }
        
        if ($this->isOption()) {

            $this->goodsOptionValidate();
        } else {
            $this->goodsValidate();
        }

        //插件下单购物车验证配置
        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.member-cart.validate');
        if ($configs) {
            foreach ($configs as $configK => $pluginConfig) {
                $class = array_get($pluginConfig,'class');
                $function =array_get($pluginConfig,'function');
                if(class_exists($class) && method_exists($class,$function) && is_callable([$class,$function])){
                    $class::$function($this);

                }

            }
        }


    }

    /**
     * 商品购买验证
     * @throws AppException
     */
    public function goodsValidate()
    {

        if (!$this->goods->stockEnough($this->total)) {
            throw new AppException('(ID:' . $this->goods_id . ')'.$this->goods->title.',库存不足');
        }
    }

    /**
     * 规格验证
     * @throws AppException
     */
    public function goodsOptionValidate()
    {
        if (!$this->goods->has_option) {
            throw new AppException('(ID:' . $this->goods_id . ')商品未启用规格');
        }
        if (!isset($this->goodsOption)) {
            throw new AppException('(ID:' . $this->goods_id . ')未找到商品规格或已经删除');
        }

        if ($this->goods_id != $this->goodsOption->goods_id) {
            throw new AppException('规格('.$this->option_id.')'.$this->goodsOption->title.'不属于商品('.$this->goods_id.')'.$this->goods->title);
        }

        if (!$this->goodsOption->stockEnough($this->total)) {
            throw new AppException('(ID:' . $this->goods_id . ')规格'.$this->goodsOption->title.',库存不足');
        }


    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'uid');
    }

    /**
     * 获取购物车分组id
     * @return int
     */
    public function getGroupId()
    {
        // 判断是否拆单。如果开启商品拆单，则将每种商品拆成不同订单，不考虑规格数量.只拆商城的商品订单
        if ($this->goods->plugin_id == 0 && !request()->is_shop_pos && \Setting::get('shop.order.order_apart')){
            return $this->goods_id;
        }

        // 厂家拆单
        if (!is_null($this->goods->producerGoods)) {
            if ($this->goods->producerGoods->switch == 1) {
                return $this->goods->producerGoods->producer_id;
            }
        }

        if (!$this->goods->getPlugin()) {
            return 0;
        }
        if (!$this->goods->getPlugin()->app()->bound(MemberCart::class)) {
            return 0;
        }
        return $this->goods->getPlugin()->app()->make(MemberCart::class, [$this->getAttributes()])->getGroupId();
    }
}