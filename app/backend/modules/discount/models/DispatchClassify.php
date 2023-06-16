<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 16:51
 */

namespace app\backend\modules\discount\models;


use app\backend\modules\goods\models\Category;
use app\common\models\BaseModel;

class DispatchClassify extends BaseModel
{
    public $table = 'yz_dispatch_classify';
    public $guarded = [''];

    public $casts = [
        'discount_value' => 'json'
    ];

    public static function classify($id)
    {
        $record=self::find($id);
        $second = [];
        $third = [];
        $first = [];
        if($record){
            $ids=explode(',',$record['dispatch_id']);
            $cate = (new Category())->getAllCategorys()->get()->toArray();
            foreach($ids as $v){
                $key=array_search($v,array_column($cate,'id'));
                if($key!==false){

                    switch($cate[$key]['level']){
                        case 2:
                            $second[]=$cate[$key];
                            break;
                        case 3:
                            $third[]=$cate[$key]['id'];
                            break;
                    }
                }
            }
            foreach($second as $sec){
                $key=array_search($sec['parent_id'],array_column($cate,'id'));
                if($key!==false){
                    $first[]=$cate[$key]['id'];
                }
            }

        }
        return [array_values(array_unique($first)),array_column($second,'id'),$third];
    }
}