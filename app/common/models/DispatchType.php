<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/28
 * Time: 上午11:32
 */

namespace app\common\models;


/**
 * Class DispatchType
 * @package app\common\models
 * @property int id
 * @property int need_send
 * @property int code
 * @property int name
 * @property int sort
 * @property int disable
 */
class DispatchType extends BaseModel
{
    public $table = 'yz_dispatch_type';
    protected $guarded = ['id'];

    const EXPRESS = 1; // 快递
    const SELF_DELIVERY = 2; // 自提
    const STORE_DELIVERY = 3; // 门店配送
    const HOTEL_CHECK_IN = 4; // 酒店入住
    const DELIVERY_STATION_SELF = 5; // 配送站自提

    const DELIVERY_STATION_SEND = 6; // 配送站送货

    const DRIVER_DELIVERY = 7; //司机配送

    const PACKAGE_DELIVER = 8; //自提点

    const CLOUD_WAREHOUSE = 9; //云仓

    const SUPPLIER_DRIVER_DISTRIBUTION = 10; //供应商订单配送

    const CITY_DELIVERY = 11; //同城配送

    const STORE_PACKAGE_DELIVER = 12; //门店自提点

    const STORE_POS = 13; //门店POS收银

    const SHOP_POS = 14; //pos收银

    const PACKAGE_DELIVERY = 15; //商城自提

//    public $appends = ['name'];
    public function needSend()
    {
        return $this->need_send;
    }

    public function getNameAttribute()
    {
        if ($this->hasOneSet) {
            return $this->hasOneSet->name ?: $this->attributes['name'];
        }
        return $this->attributes['name'];
    }


    /**
     * 订单支付就直接完成的配送方式
     * @return array
     */
    public function paidCompleted()
    {
        return [self::CLOUD_WAREHOUSE, self::SHOP_POS];
    }


    public static function goodsEnableDispatchTypeIds(array $ids)
    {
        if (empty($ids)) {return [];}

        $dispatchTypes = self::whereIn('id', $ids)->with(['hasOneSet'])->get();

        $dispatchTypes  = $dispatchTypes->map(function ($item) {
            if (!is_null($item->hasOneSet)) {
                $item->enable = $item->hasOneSet->enable;
                $item->sort = $item->hasOneSet->sort;
            }
            return $item;
        })->filter(function ($dispatchType) {
            return $dispatchType->enable;
        })->values();

        return $dispatchTypes->pluck('id')->toArray();
    }

    public static function dispatchTypesSetting($dispatchTypes = null)

    {
        $dispatchTypes = $dispatchTypes ?: DispatchType::getCurrentUniacidSet()->toArray();
        $dispatchTypes = array_combine(array_column($dispatchTypes, 'code'), $dispatchTypes);


        $dispatchTypes = array_filter($dispatchTypes, function ($dispatchTypes) {
            return $dispatchTypes['enable'];
        });

        $dispatchTypesSetting = array_sort($dispatchTypes, function ($dispatchType) {
            return $dispatchType['sort'] + $dispatchType['id'] / 100;
        });

        return $dispatchTypesSetting;
    }

    public static function getCurrentUniacidSet($plugin = 0)
    {
        if (is_array($plugin)) {
            $dispatchTypes = self::whereIn('plugin', $plugin)->with(['hasOneSet'])->get();
        } else {
            $dispatchTypes = self::where('plugin', $plugin)->with(['hasOneSet'])->get();
        }

        $dispatchTypes = $dispatchTypes->filter(function ($item) {
                    return $item->getPluginEnable();
                })->map(function ($item) {
                if (!is_null($item->hasOneSet)) {
                    $item->enable = $item->hasOneSet->enable;
                    $item->sort = $item->hasOneSet->sort;
                }
                $item->name = $item->getTypeName();

                return $item;
            })->sortByDesc('sort')->values();
//        $dispatchTypes = $dispatchTypes->map(function ($item) {
//            if (!is_null($item->hasOneSet)) {
//                $item->enable = $item->hasOneSet->enable;
//                $item->sort = $item->hasOneSet->sort;
//            }
//            $item->name = $item->getTypeName();
//
//            return $item;
//        })->sortByDesc('sort')->values();

        return $dispatchTypes;
    }

    public static function getAllEnableDispatchType()
    {
        $dispatchTypes = self::where('enable', 1)->with(['hasOneSet'])->get();

        $dispatchTypes =  $dispatchTypes->map(function ($item) {
            if (!is_null($item->hasOneSet)) {
                $item->enable = $item->hasOneSet->enable;
                $item->sort = $item->hasOneSet->sort;
            }
            return $item;
        })->filter(function ($dispatchType) {
            return $dispatchType->enable;
        })->values();

        return $dispatchTypes;
    }

    //todo 临时加自定义名称
    public function getTypeName()
    {
        $type_name = $this->name;
        switch ($this->id) {
            case self::PACKAGE_DELIVER : $type_name = PackageDeliver; break;
        }
        return $type_name;
    }
    //判断是否有这种配送方式
    public function getPluginEnable()
    {
        if ($this->plugin_id) {
            $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-dispatch-menu')[$this->plugin_id][$this->code];
        } else {
            $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-dispatch-menu.shop')[$this->code];
        }
        return !is_null($configs);
    }
    

    public function hasOneSet()
    {
        return $this->hasOne(DispatchTypeSet::class, 'dispatch_type_id', 'id'); // TODO: Change the autogenerated stub
    }

}