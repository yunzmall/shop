<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/11/18
 * Time: 16:42
 */

namespace app\common\modules\goods\events;


use app\common\events\Event;
use app\common\models\Goods;

class GoodsStockChangeEvent extends Event
{

    protected $operation_type;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var Goods
     */
    protected $goods;

    public function __construct(Goods $goods,array $parameters = [], $operation_type = '')
    {

        $this->goods = $goods;
        $this->parameters = $parameters;

        $this->operation_type = $operation_type;

    }
    public function getType()
    {
        return $this->operation_type;
    }

    final public function getGoods()
    {
        return $this->goods;
    }

    final public function getParameters()
    {
        return $this->parameters;
    }
}