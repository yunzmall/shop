<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/5/26
 * Time: 11:12
 */

namespace app\frontend\modules\cart\models;


use app\common\models\BaseModel;

class CartBaseModel extends BaseModel
{
    public function __construct(array $attributes = [])
    {
        $this->setInitialAttributes($attributes);

        parent::__construct([]);
    }

    /**
     * 模型参数初始赋值
     * @param $data
     */
    protected function setInitialAttributes($data)
    {
        if (!empty($data)) {
            $this->setRawAttributes($data);
        }
    }
}