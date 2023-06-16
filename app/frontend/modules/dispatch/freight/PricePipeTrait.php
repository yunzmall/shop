<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/12
 * Time: 14:03
 */

namespace app\frontend\modules\dispatch\freight;


trait PricePipeTrait
{
    /**
     * @var Collection
     */
    public $priceCache;
    /**
     * @var Collection
     */
    private $pricePipes;

    /**
     * @return Collection
     */
    public function getPricePipes()
    {
        if (!isset($this->pricePipes)) {
            $this->pricePipes = $this->_getPricePipes();
        }
        return $this->pricePipes;
    }

    public function pushPipe($pipe)
    {
         $this->getPricePipes()->push($pipe);
    }


    public function _getPricePipes()
    {
        return collect([]);
    }


    /**
     * 获取某个节点之后的价格
     * @param $key
     * @return mixed
     * @throws AppException
     */
    public function getPriceAfter($key)
    {
        if (!isset($this->priceCache[$key])) {
            // 找到对应的节点
            $priceNode = $this->getPricePipes()->first(function (PriceNode $priceNode) use ($key) {
                return $priceNode->getKey() == $key;
            });
            if (!$priceNode) {
                throw new AppException("不存在的价格节点{$key}");
            }
            $this->priceCache[$key] = $priceNode->getPrice();
        }
        return $this->priceCache[$key];
    }
}