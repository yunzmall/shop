<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/12/20
 * Time: 11:39
 */

namespace app\common\services\plugin\store;

use Yunshop\Integral\Common\Models\IntegralGoodsModel;
use Yunshop\Love\Common\Models\GoodsLove;
use Yunshop\Love\Common\Services\SetService;
use Yunshop\StoreCashier\common\models\StoreSetting;
use Yunshop\StoreIntegralFees\models\StoreIntegralFeesGoods;

class GoodsDeductionShowService
{
    private $goods_ids;

    protected $goodsLoves;

    protected $integral_sign;
    protected $integral_set;
    protected $integral_deduction_set;

    protected $love_sign;
    protected $love_activation_set;
    protected $love_set;

    protected $store_integral_fees_sign;
    protected $store_integral_fees_goods_set;
    protected $store_integral_fees_set;

    public function __construct($goodsIds = array())
    {
        $this->goods_ids = $goodsIds;

        //消费积分
        $this->integral_sign = false;
        if (app('plugins')->isEnabled('integral')) {
            //获取消费积分抵扣统一设置
            $integralGoods = IntegralGoodsModel::select(['id', 'goods_id', 'deduction_set', 'deduction_set_min', 'deduction_set_max'])->whereIn('goods_id', $goodsIds)->get()->toArray();

            $this->integral_sign = true;
            $this->integral_deduction_set = array_column($integralGoods, null, 'goods_id');
            $this->integral_set = [
                'deduction_set' => \Setting::get('integral.deduction_set'),
                'deduction_set_min' => \Setting::get('integral.deduction_set_min'),
                'deduction_set_max' => \Setting::get('integral.deduction_set_max')
            ];
        }

        //爱心值
        $this->love_sign = false;
        if (app('plugins')->isEnabled('love')) {
            $loveGoods = GoodsLove::select(['id', 'goods_id', 'activation_state', 'award_proportion','love_accelerate','award','deduction_proportion','deduction'])->whereIn('goods_id', $goodsIds)->get()->toArray();

            $this->love_sign = true;
            $this->love_activation_set = array_column($loveGoods, null, 'goods_id');
            $this->love_set = [
                'activation_state' => SetService::getAcceleratedActivationStatus(),//开关
                'love_accelerate' => SetService::getAcceleratedActivationOfLoveRatio(),//激活比例
                'reward_state' => SetService::getAwardStatus(),//购物赠送开关,
                'deduction_state' => SetService::getDeductionStatus(),//购物抵扣开关
            ];
        }
        $this->goodsLoves = collect($loveGoods ?? []);

        //手续费
        $this->store_integral_fees_sign = false;
        if (app('plugins')->isEnabled('store-integral-fees')) {
            $store_integral_fees_set = \Setting::get('plugin.store_integral_fees');
            $storeIntegralFees = StoreIntegralFeesGoods::select(['id', 'goods_id', 'is_open', 'fees_rate'])->whereIn('goods_id', $goodsIds)->get()->toArray();

            $this->store_integral_fees_sign = true;
            $this->store_integral_fees_goods_set = array_column($storeIntegralFees, null, 'goods_id');
            $this->store_integral_fees_set = [
                'is_open' => $store_integral_fees_set['is_plugin_open'],//开关
                'store_integral_fees' => $store_integral_fees_set['store_integral_fees'],
            ];
        }
    }

    /**
     * 商品内容显示：消费积分 爱心值 手续费等前端显示
     * @param int $showType 1-首页-装修（不受基础设置控制），2-门店商品内容显示（受门店基础设置控制）
     * @param int $returnType 返回类型，1-key,name,value形式（前端需要的格式），2-商品详情活动需要格式
     * @return array
     */
    public function getDeductionShow($showType = 1, $returnType = 1): array
    {
        $storeCashierSign = app('plugins')->isEnabled('store-cashier');
        $res = $frontendData = [];
        $show_type = [
            'integral' => true,
            'love' => true,
            'store_integral_fees' => true,
            'love_reward' => true,
            'love_deduction' => true,
        ];

        //门店商品内容显示
        if ($showType != 1) {
            $store_set = \Setting::get('plugin.store');
            $show_plugins = [
                'integral' => $store_set['goods_show_integral'],
                'love' => $store_set['goods_show_love'],
                'love_reward' => $this->love_set['reward_state'] && $store_set['goods_show_love_reward'] ? 1 : 0,
                'love_deduction' => $this->love_set['deduction_state'] && $store_set['goods_show_love_deduction'] ? 1 : 0,
                'store_integral_fees' => $store_set['goods_show_store_integral_fees']
            ];

            foreach ($show_plugins as $key => $value) {
                //未开启
                if ($value != 1) {
                    $show_type[$key] = false;//不显示
                }
            }
        }


        foreach ($this->goods_ids as $key => $item) {
            //消费积分抵扣显示
            if ($this->integral_sign && $show_type['integral']) {
                $integral_name = \Yunshop\Integral\Common\CommonService::getPluginName();
                $res[$item]['integral'] = $this->getIntegralData($item);
                if ($res[$item]['integral']['max_deduction'] > 0) {
                    $frontendData[$item][] = $this->getFrontendData('integral', $integral_name.'抵扣', $res[$item]['integral']['max_deduction']);
                }
            }

            if ($this->love_sign){
                $love_name = \Setting::get('love.name') ?: '爱心值';


            //爱心值激活比例显示
            if ($show_type['love']) {
                $res[$item]['love'] = $this->getLoveData($item);
                if ($res[$item]['love']['love_accelerate'] > 0) {
                    $frontendData[$item][] = $this->getFrontendData('love', $love_name.'激活', $res[$item]['love']['love_accelerate']);
                }
            }


                if ($show_type['love_reward']) {
                    $percent = 0;
                    if ($goods_love = $this->goodsLoves->where('award', 1)->where('goods_id', $item)->first()) {
                        if ($goods_love['award_proportion']) {
                            $percent = bcadd($goods_love['award_proportion'], 0, 2);
                        } else {
                            $percent = SetService::getAwardProportion();
                            if ($storeCashierSign && $store_setting = StoreSetting::whereHas('hasOneStoreGoods', function ($query) use ($item) {
                                $query->where('goods_id', $item);
                            })->where('key', 'love')->first()) {
                                if ($store_setting->value['award_proportion'] && (bccomp($store_setting->value['award_proportion'], 0, 2) == 1)) {
                                    $percent = $store_setting->value['award_proportion'];
                                }
                            }
                        }
                    }

                    if ($percent && (bccomp($percent, 0, 2) == 1)) {
                        $res[$item]['love_reward']['award_percent'] = $percent;
                        $award_type = SetService::getAwardType();
                        if ($award_type == 'froze') {
                            $love_award_name = \Setting::get('love.unable_name') ?: $love_name;
                        } elseif ($award_type == 'usable') {
                            $love_award_name = \Setting::get('love.usable_name') ?: $love_name;
                        }
                        $frontendData[$item][] = $this->getFrontendData('love_reward', $love_award_name . '赠送', $percent);
                    }
                }

                if ($show_type['love_deduction']) {
                    $max_percent = 0;
                    if ($goods_love =  $this->goodsLoves->where('deduction', 1)->where('goods_id', $item)->first()) {
                        if ($goods_love['deduction_proportion']) {
                            $max_percent = bcadd($goods_love['deduction_proportion'], 0, 2);
                        } else {
                            $max_percent = SetService::getDeductionProportion();
                            if ($storeCashierSign && $store_setting = StoreSetting::whereHas('hasOneStoreGoods', function ($query) use ($item) {
                                $query->where('goods_id', $item);
                            })->where('key', 'love')->first()) {
                                if ($store_setting->value['deduction_proportion'] && (bccomp($store_setting->value['deduction_proportion'], 0, 2) == 1)) {
                                    $max_percent = $store_setting->value['deduction_proportion'];
                                }
                            }
                        }
                    }

                    if ($max_percent && (bccomp($max_percent, 0, 2) == 1)) {
                        $love_deduction_name = \Setting::get('love.usable_name') ?: $love_name;
                        $res[$item]['love_deduction']['deduction_percent'] = $max_percent;
                        $frontendData[$item][] = $this->getFrontendData('love_deduction', $love_deduction_name . '抵扣', $max_percent);
                    }

                }

            }
            //消费积分手续费显示
            if ($this->store_integral_fees_sign && $show_type['store_integral_fees']) {
                $res[$item]['store_integral_fees'] = $this->getStoreIntegralFeesData($item);
                if ($res[$item]['store_integral_fees']['fees_rate'] > 0) {
                    $frontendData[$item][] = $this->getFrontendData('store-integral-fees', '手续费', $res[$item]['store_integral_fees']['fees_rate']);
                }
            }
        }

        if ($returnType == 1) {
            return $frontendData;
        } elseif ($returnType == 2) {
            return $res;
        } else {
            return [];
        }
    }

    //消费积分
    protected function getIntegralData($goodsId): array
    {
        $deduction_set_min = $deduction_set_max = 0;

        //独立商品 -- 开启
        if ($this->integral_set['deduction_set'] && $this->integral_deduction_set[$goodsId]['deduction_set']) {
            $deduction_set_min = $this->integral_deduction_set[$goodsId]['deduction_set_min'] ?: 0;//商品-最低
            $deduction_set_max = $this->integral_deduction_set[$goodsId]['deduction_set_max'] ?: 0;//商品-最高

            if (empty($deduction_set_min)) {
                $deduction_set_min = $this->integral_set['deduction_set_min'] ?: 0;//统一最低
            }
            if (empty($deduction_set_max)) {
                $deduction_set_max = $this->integral_set['deduction_set_max'] ?: 0;//统一最高
            }
        }

        $max_deduction = $deduction_set_max;
        if ($deduction_set_min > $deduction_set_max) {
            $max_deduction = $deduction_set_min;
        }

        return [
            'max_deduction' => (float)$max_deduction,//最高的抵扣
            'deduction_set_min' => (float)$deduction_set_min,
            'deduction_set_max' => (float)$deduction_set_max
        ];
    }

    //爱心值
    protected function getLoveData($goodsId): array
    {
        $love_accelerate = 0;

        //独立商品 -- 开启
        if ($this->love_set['activation_state'] && $this->love_activation_set[$goodsId]['activation_state']) {
            $love_accelerate = $this->love_activation_set[$goodsId]['love_accelerate'] ?: 0;//商品-激活爱心值比例

            if (empty($love_accelerate)) {
                $love_accelerate = $this->love_set['love_accelerate'] ?: 0;//统一激活爱心值比例
            }
        }

        return [
            'love_accelerate' => (float)$love_accelerate
        ];
    }

    //消费积分手续费
    protected function getStoreIntegralFeesData($goodsId): array
    {
        $fees_rate = 0;

        //独立商品 -- 开启
        if ($this->store_integral_fees_set['is_plugin_open'] && $this->store_integral_fees_goods_set[$goodsId]['is_open']) {
            $fees_rate = $this->store_integral_fees_goods_set[$goodsId]['fees_rate'] ?: 0;//商品-抵扣收取手续费比例

            if (empty($fees_rate)) {
                $fees_rate = $this->store_integral_fees_set['fees_rate'] ?: 0;//统一抵扣手续费比例
            }
        }

        return [
            'fees_rate' => (float)$fees_rate
        ];
    }

    private function getFrontendData($key, $name, $value)
    {
        return [
            'key' => $key,
            'name' => $name,
            'value' => $value
        ];
    }
}