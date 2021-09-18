<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/11
 * Time: 11:31
 */

namespace app\common\models\goods;

use app\common\models\BaseModel;
use app\common\models\Goods;

class GoodsAdvertising extends BaseModel
{
    protected $table = 'yz_goods_advertising';

    protected $guarded = [''];

    public static function getGoodsData($goods_id)
    {
        return self::uniacid()
            ->where('goods_id', $goods_id)
            ->first();
    }

    public function goods(){
        return $this->belongsTo(Goods::class);
    }
}