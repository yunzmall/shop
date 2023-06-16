<?php
namespace app\backend\modules\goods\models;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/27
 * Time: 上午9:18
 */


class Brand extends \app\common\models\Brand
{
    static protected $needLog = true;

    /**
     * @param $id
     * @return mixed
     */
    public static function deletedBrand($id)
    {
        return self::where('id', $id)
            ->delete();
    }

//    public function keywordGetBrand($keyword)
//    {
//        return self::uniacid()->where('name','like', '%'.$keyword .'%')->select(['id','name']);
//    }

    /**
     *  定义字段名
     * 可使
     * @return array */
    public  function atributeNames() {
        return [
            'name'=> '品牌名称',
        ];
    }
    
    /**
     * 字段规则
     * @return array */
    public  function rules() {
        return [
            'name' => 'required',
        ];
    }
}