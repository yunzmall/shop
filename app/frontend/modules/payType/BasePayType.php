<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/6/7
 * Time: 下午4:45
 */

namespace app\frontend\modules\payType;

use app\common\exceptions\AppException;
use app\common\models\OrderPay;
use app\common\models\PayType;

class BasePayType extends PayType implements OrderPayInterface
{
    /**
     * @var OrderPay
     */
    protected $orderPay;

    public function setOrderPay(OrderPay $orderPay)
    {
        $this->orderPay = $orderPay;

    }

    public function applyPay()
    {
    }

    /**
     * @param array $option
     * @return array
     * @throws AppException
     */
    public function getPayParams($option)
    {
        $extra = ['type' => 1];

        if (!is_array($option)) {
            throw new AppException('参数类型错误');
        }

        $extra = array_merge($extra, $option);

//        $amount = 0;
//        foreach ($this->orderPay->orders as $o) {
//            $amount = bcadd($o->price, $amount, 2);
//
//        }

        $goods_title = $this->orderPay->orders->first()->hasManyOrderGoods[0]->pay_title;

        return [
            'order_no' => $this->orderPay->pay_sn,
            'amount' => bcadd($this->orderPay->orders->sum('price'), 0, 2),
            'subject' =>  $goods_title?$this->cutStr($goods_title,80): ' ',
            'body' => ($goods_title ?$this->cutStr($goods_title,80,false): ' ') . ':' . \YunShop::app()->uniacid,
            'extra' => $extra
        ];
    }


    /**
     * @param $sourcestr string 字符串
     * @param $cutlength integer 保留长度
     * @return string
     */
    public function cutStr($sourcestr, $cutlength, $ellipsis = true) {
        $returnstr = '';
        $i = 0;
        $n = 0;
        $str_length = strlen ($sourcestr); //字符串的字节数
        while ( ($i < $cutlength) and ($i <= $str_length) ) {
            $temp_str = substr ( $sourcestr, $i, 1);
            $ascnum = Ord ($temp_str); //得到字符串中第$i位字符的ascii码
            if ($ascnum >= 224) {
                //如果ASCII位高与224
                $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i = $i + 3; //实际Byte计为3
                $n ++; //字串长度计1
            } elseif ($ascnum >= 192) {
                //如果ASCII位高与192，
                $returnstr = $returnstr . substr ( $sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i = $i + 2; //实际Byte计为2
                $n ++; //字串长度计1
            } elseif ($ascnum >= 65 && $ascnum <= 90) {
                //如果是大写字母，
                $returnstr = $returnstr . substr ( $sourcestr, $i, 1);
                $i = $i + 1; //实际的Byte数仍计1个
                $n ++; //但考虑整体美观，大写字母计成一个高位字符
            } else {
                //其他情况下，包括小写字母和半角标点符号，
                $returnstr = $returnstr . substr ( $sourcestr, $i, 1);
                $i = $i + 1; //实际的Byte数计1个
                $n = $n + 0.5; //小写字母和半角标点等与半个高位字符宽...
            }
        }
        if ($str_length > $cutlength && $ellipsis) {
            $returnstr = $returnstr . "..."; //超过长度时在尾处加上省略号
        }
        return $returnstr;
    }
}