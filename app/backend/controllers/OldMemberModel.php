<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2021/3/24
 * Time: 9:59
 */

namespace app\backend\controllers;


use app\common\models\BaseModel;

class OldMemberModel extends BaseModel
{
    public $table = 'sz_yi_member';
    public $guarded = [''];

}