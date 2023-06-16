<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/3/20
 * Time: 上午10:41
 */

namespace app\common\models;

use app\backend\models\BackendModel;

class Migration extends BackendModel
{
    public $table = 'migrations';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $guarded = ['id'];
}