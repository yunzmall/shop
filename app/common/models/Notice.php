<?php
namespace app\common\models;

use app\backend\models\BackendModel;
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/27
 * Time: 上午9:11
 */

class Notice extends BackendModel
{
    public $table = 'yz_goods_notices';

    protected $guarded = [''];

    protected $fillable = [''];
    
    public function hasOneMini()
    {
        return $this->hasOne(MemberMiniAppModel::class,'member_id','uid');
    }
}
