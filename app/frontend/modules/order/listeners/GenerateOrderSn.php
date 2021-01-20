<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/14
 * Time: 14:08
 */

namespace app\frontend\modules\order\listeners;


use Illuminate\Support\Facades\Redis;

class GenerateOrderSn
{
    public function handle()
    {
        $redis =  new Redis();

        //集合总数
        $count =  $redis::sCard('order_sn');

        if ($count < 100000) {
            $difference = 100000 - $count;
            $i = 0;
            while (1) {
                //添加成员
                $v1 = substr(uniqid(), 7, 13);
                $date = date('ymdHi');
                $add = $redis::sAdd('order_sn','SN'.$date.strtoupper($v1));
                $i += $add;
                if ($i >= $difference) {
                    break;
                }
            }
            //集合中是否存在该值
            //$bool =  $redis::sIsMember('order_sn', $value);

            //显示集合中所以成员
            //$list = $redis::sMembers('order_sn');

            //随机返回集合中的一个元素
            //$a = $redis::sPop('order_sn');
        }
    }

    public function a()
    {
        $v1 = substr(uniqid(), 7, 13);
        $v2 = str_split($v1,1);
        $v3 = array_map('ord', $v2);
        $v4 = implode(null, $v3);
        $value = substr($v4,0,8);
    }
}