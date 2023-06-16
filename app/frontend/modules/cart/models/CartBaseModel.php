<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
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