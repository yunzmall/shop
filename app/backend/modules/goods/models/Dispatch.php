<?php
/**
 * 配送模板数据操作
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/24
 * Time: 下午2:31
 */

namespace app\backend\modules\goods\models;


class Dispatch extends \app\common\models\goods\Dispatch
{
    static protected $needLog = true;

    /**
     * 获取配送模板所有数据
     * @param int $goodsId
     * @return array
     */
    public static function getList()
    {
        return self::uniacid()
            ->get();
    }
    public static function getAll()
    {
        return self::getDispatchList();
    }

    public static function getTemplate(){
        return self::getDispatch();
    }
    /**
     * 获取配送模板单条数据
     * @param int $goodsId
     * @return array
     */
    public static function getOne($id)
    {
        return self::where('id', $id)
            ->first();
    }

    /**
     * 获取配送模板单条数据
     * @param int $goodsId
     * @return array
     */
    public static function getOneByDefault()
    {
        return self::uniacid()->where('is_default', 1)
            ->first();
    }

    /**
     * 配送模板数据添加
     * @param array $DispatchInfo
     * @return bool
     */
    public static function createdDispatch($DispatchInfo)
    {
        return self::insert($DispatchInfo);
    }

    /**
     * 配送模板数据更新
     * @param array $DispatchInfo
     * @return mixed
     */
    public static function updatedDispatch($dispatchId, $DispatchInfo)
    {
        return self::where('id', $dispatchId)->update($DispatchInfo);
    }

    /**
     * 配送模板数据删除
     * @param int $goodsId
     * @return mixed
     */
    public static function deletedDispatch($dispatchId)
    {
        return self::where('id', $dispatchId)->delete();
    }
    public static function quickUpdatedDispatch($id, $type,$status)
    {
        if ($type == 'is_default' && $status){
            self::uniacid()->where('is_default', 1)->update(['is_default' => 0]);
        }
        return self::where('id', $id)->update([$type => $status]);
    }


}