<?php
namespace app\common\models;


/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/27
 * Time: 上午9:11
 */
class Store extends BaseModel
{

    public $table = 'yz_store';

    public function hasOneMember()
    {
        return $this->hasOne(Member::class, 'uid', 'uid');
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public static function getStoreByName($keyword)
    {
        return static::uniacid()->select('id', 'store_name', 'thumb')
            ->where('store_name', 'like', '%' . $keyword . '%')

            ->get();
    }
}
