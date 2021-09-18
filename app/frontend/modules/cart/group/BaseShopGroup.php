<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/7
 * Time: 14:30
 */

namespace app\frontend\modules\cart\group;


use app\common\models\MemberCart;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseShopGroup
{

    protected $memberCart;


    public function __construct($memberCart)
    {
        $this->memberCart = $memberCart;
    }


    abstract public function getGroupId();

    /**
     * @return boolean
     */
    abstract function validate();


    /**
     * 插件文件名称
     * @return mixed
     */
    abstract function getPluginPathName();


    public function getWeight()
    {
        return 0;
    }

    public function goods()
    {
        return $this->memberCart->goods;
    }


    public function getShopId(){}

    public function getName(){}


}