<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022-09-21
 * Time: 14:02
 */

namespace app\common\models\member;


use app\common\models\BaseModel;

class MemberMerge extends BaseModel
{
    public $table = 'yz_member_merge';
    public $guarded = [''];
    public $appends = ['merge_type_name'];

    public function getMergeTypeNameAttribute()
    {
        $name = '';
        switch ($this->merge_type) {
            case 1 : $name = '绑定手机合并';
                break;
            case 2 : $name = '点击合并';
                break;
            case 3 : $name = '自动合并';
                break;
            case 4 : $name = '后台合并';
                break;
        }
        return $name;
    }

    public static function search($search)
    {
        $model = self::uniacid();
        if ($search['before_uid']) {
            $model->where('before_uid', $search['before_uid']);
        }
        if ($search['after_uid']) {
            $model->where('after_uid', $search['after_uid']);
        }
        return $model;
    }
}