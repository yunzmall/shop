<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/12
 * Time: 下午1:38
 */

namespace app\common\models\systemMsg;

use app\common\models\BaseModel;
use app\common\services\SystemMsgService;

/**
 * Class SysMsgLog
 * @package app\common\models\systemMsg
 */
class SysMsgLog extends BaseModel
{
    protected $table = 'yz_sys_msg_log';
    protected $guarded = [];

    public function setRedirectParamAttribute($value)
    {
        $this->attributes['redirect_param'] = json_encode($value);
    }

    public function getRedirectParamAttribute($value)
    {
        return json_decode($value,true);
    }

    public function getRedirectUrlAttribute($value)
    {
        return SystemMsgService::$url[$value]?yzWebUrl(SystemMsgService::$url[$value],$this->redirect_param):$value;
    }

    /**
     * 获取该消息所属的消息类型。
     */
    public function belongsToType()
    {
        return $this->belongsTo('app\common\models\systemMsg\SysMsgType','type_id');
    }

    public static function getLogList($type_id = 0,$search = [])
    {
        $model = self::uniacid()->selectRaw('id,uniacid,type_id,title,content,redirect_url,redirect_param,is_read,created_at,read_at');
        if(!empty($type_id)){
            $model->where('type_id',$type_id);
        }
        if(isset($search['is_read']) && is_numeric($search['is_read']) && in_array($search['is_read'],[0,1]))
        {
            $model->where('is_read',$search['is_read']);
        }
        if(!empty($search['time'])){
            $range = [$search['time']['start'], $search['time']['end']];
            $model->whereBetween('created_at', $range);
        }
        return $model;
    }

    //获取总共未读的数量
    public static function getLogCount()
    {
        $model = self::uniacid();
        return $model->where('is_read',0)->count();
    }


}