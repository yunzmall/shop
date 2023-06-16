<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/9
 * Time: 14:10
 */

namespace app\platform\modules\system\models;

use app\common\models\BaseModel;

/**
 * Class WhiteList
 * @package app\platform\modules\system\models
 * @property string ip
 * @property int is_open
 */
class WhiteList extends BaseModel
{
    public $table = 'yz_system_white_list';
    public $timestamps = true;
    protected $guarded = [''];

    /**
     * @param array $search
     * @return WhiteList
     */
    public static function getWhite($search = [])
    {
        $model = new self();
        if ($search['id']) {
            $model = $model->where('id',$search['id']);
        }
        if ($search['ip']) {
            $model = $model->where('ip',$search['ip']);
        }
        if (isset($search['is_open']) && is_numeric($search['is_open'])) {
            $model = $model->where('is_open',$search['is_open']);
        }
        return $model;
    }

    /***
     * @param $ip
     * @return mixed
     */
    public static function checkValidateIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public static function addIP($data = [])
    {
        if (!$data || !is_array($data)) {
            throw new \Exception('参数错误');
        }
        $insert = [];
        foreach ($data as $ip) {
            if(!self::checkValidateIP($ip)) {
                throw new \Exception('输入的ip地址不合法');
            }
            $insert[] = [
                'ip' => $ip,
                'is_open' => 1, //默认开启
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        if ($insert) {
            $res = self::insert($insert);
            return $res;
        }
        return false;
    }

    public static function editIP($id,$data)
    {
        if (!$id) {
            throw new \Exception('参数错误');
        }
        $ip = self::getWhite(['id'=>$id])->first();
        if (!$ip) {
            throw new \Exception('未找到该白名单');
        }
        $fill = [];
        //编辑ip
        if ($data['ip'] && !self::checkValidateIP($data['ip'])) {
            throw new \Exception('输入的ip地址不合法');
        }
        $data['ip'] && $fill['ip'] = $data['ip'];
        //编辑状态
        if (isset($data['is_open'])) {
            $fill['is_open'] = $data['is_open'] ? 1 : 0;
        }
        if (!$fill) {
            throw new \Exception('参数错误');
        }
        $ip->fill($fill);
        return $ip->save();
    }

    public static function delIP($id)
    {
        if (!$id) {
            throw new \Exception('参数错误');
        }
        return self::where('id',$id)->delete();
    }

    /**
     * 是否白名单IP
     * @param $ip
     * @return bool
     */
    public static function isWhite($ip)
    {
        return self::getWhite(['ip' => $ip,'is_open' => 1])->count() ? true : false;
    }
}