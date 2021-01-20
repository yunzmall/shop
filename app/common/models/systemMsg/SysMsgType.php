<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/12
 * Time: 下午1:38
 */

namespace app\common\models\systemMsg;

use app\common\models\BaseModel;

/**
 * Class SysMsgType
 * @package app\common\models\systemMsg
 */
class SysMsgType extends BaseModel
{
    protected $table = 'yz_sys_msg_type';

    /**
     * 获取类型下的所有通知
     */
    public function hasManyLog()
    {
        return $this->hasMany('app\common\models\systemMsg\SysMsgLog','type_id');
    }

}