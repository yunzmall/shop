<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/10/6
 * Time: 上午10:14
 */

namespace app\frontend\modules\deduction\models;


use app\common\models\BaseModel;
use app\common\models\VirtualCoin;
use app\common\modules\shop\ShopConfig;
use app\frontend\models\Goods;
use app\frontend\modules\deduction\DeductionSettingCollection;

/**
 * Class Deduction
 * @package app\frontend\modules\deduction\models
 * @property int id
 * @property string code
 */
class Deduction extends BaseModel
{
    protected $table = 'yz_deduction';
    private $setting;
    /**
     * @var VirtualCoin
     */
    private $coin;
    protected $guarded = [''];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function valid()
    {
        return app('DeductionManager')->make('GoodsDeductionManager')->bound($this->getCode());
    }

    //订单抵扣新增判断，抵扣虚拟币必须注册会员类
    public function memberCoin($member)
    {
        if (app('CoinManager')->make('MemberCoinManager')->bound($this->getCode())) {
            return app('CoinManager')->make('MemberCoinManager')->make($this->getCode(), [$member]);
        }

        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->getCoin()->getName();
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getCoin()
    {
        if (isset($this->coin)) {
            return $this->coin;
        }
        return $this->coin = app('CoinManager')->make($this->getCode());
    }

    /**
     * @return bool | DeductionSettingCollection
     */
    public function getSettingCollection()
    {
        if (isset($this->setting)) {
            return $this->setting;
        }
        if (!app('DeductionManager')->make('DeductionSettingManager')->bound($this->getCode())) {
            return false;
        }
        // todo 这里设计有错误, 没有区分订单范围内的抵扣设置项和全局范围内的抵扣设置项, 为了快速解决这个问题,在getDeductionSettingCollection方法中过滤掉 商品的抵扣设置

        return $this->setting = app('DeductionManager')->make('DeductionSettingManager')->make($this->getCode())->getDeductionSettingCollection(new Goods());
    }

    public function isEnableDeductDispatchPrice()
    {

        if (!$this->getSettingCollection()) {
            return false;
        }
        return $this->getSettingCollection()->isEnableDeductDispatchPrice();
    }

    /**
     * todo 这里应该是全局范围内的抵扣设置项
     * @return bool|mixed|string
     */
    public function getAffectDeductionAmount()
    {

        if (!$this->getSettingCollection()) {
            return false;
        }
        return $this->getSettingCollection()->getAffectDeductionAmount();
    }

    public function getEnable(){
        if(ShopConfig::current()->get('shop-foundation.deduction.enable') == false){
            return collect();
        }
        return Deduction::where('enable', 1)->get();
    }
}