<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/2
 * Time: 下午4:48
 */

namespace app\common\models;



use Illuminate\Database\Eloquent\SoftDeletes;

class MemberFavorite extends BaseModel
{
    use SoftDeletes;

    protected $table = 'yz_member_favorite';

}