<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/21
 * Time: 15:40
 */

namespace app\common\modules\goods\events;

use app\common\events\Event;
use app\common\models\Goods;
use app\common\models\GoodsOption;

class GoodsOptionStockChangeEvent extends Event
{

    protected $operation_type;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var Goods
     */
    protected $goodsOption;

    public function __construct(GoodsOption $goodsOption, array $parameters = [], $operation_type = '')
    {

        $this->goodsOption = $goodsOption;
        $this->parameters = $parameters;

        $this->operation_type = $operation_type;

    }

    public function getType()
    {
        return $this->operation_type;
    }

    final public function getGoodsOption()
    {
        return $this->goodsOption;
    }

    final public function getParameters()
    {
        return $this->parameters;
    }
}
