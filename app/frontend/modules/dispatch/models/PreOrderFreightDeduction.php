<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/14
 * Time: 18:47
 */

namespace app\frontend\modules\dispatch\models;


use app\common\models\order\OrderFreightDeduction;
use app\frontend\modules\order\models\PreOrder;

class PreOrderFreightDeduction extends OrderFreightDeduction
{
    private $order;


    public function setOrder(PreOrder $order)
    {
        $this->order = $order;

        $this->order->orderFreightDeduction->push($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->code = (string)$this->code;
        $this->name = (string)$this->name;
        $this->amount = sprintf('%.2f', $this->amount);
        $this->coin = sprintf('%.2f', $this->coin);
        return parent::toArray();
    }


    /**
     * @return bool
     */
    public function beforeSaving()
    {
//        if (!$this->isChecked()) {
//            return false;
//        }
        $this->code = (string)$this->code;
        $this->name = (string)$this->name;
        $this->amount = sprintf('%.2f', $this->amount);
        $this->coin = sprintf('%.2f', $this->coin);
        return parent::beforeSaving();
    }

}