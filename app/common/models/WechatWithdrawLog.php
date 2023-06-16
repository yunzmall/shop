<?php

namespace app\common\models;

use app\common\traits\CreateOrderSnTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class WechatWithdrawLog
 * @package app\common\models
 * @property int uniacid
 * @property string withdraw_sn
 * @property int member_id
 * @property string out_batch_no
 * @property string out_detail_no
 * @property string batch_id
 * @property string batch_status
 * @property string detail_id
 * @property string detail_status
 * @property string http_code
 * @property string fail_code
 * @property string fail_msg
 * @property int status
 * @property int type
 * @property int pay_type
 * @method static self search(array $search=[],array $select = ['*'])
 */
class WechatWithdrawLog extends BaseModel
{
    use CreateOrderSnTrait;

    protected $table = 'yz_wechat_withdraw_log';
    public $timestamps = true;
    protected $guarded = [];
    protected $appends = [];

    /**
     * 需要重试的状态
     */
    const REPEAT_STATUS = [
        'SYSTEM_ERROR','NOT_ENOUGH','FREQUENCY_LIMITED'
    ];

    /**
     * @param $fill
     * @return WechatWithdrawLog
     * @throws \Exception
     */
    public static function saveLog($fill = [])
    {
        $model = new self();
        $model->fill($fill);
        if (!$model->save()) {
            throw new \Exception('保存记录失败');
        }
        return $model;
    }

    public static function updateModel(WechatWithdrawLog $log,$update = [])
    {
        if (!$update) {
            throw new \Exception('修改数据为空');
        }
        $log->update($update);
    }

    /**
     * @param Builder $query
     * @param array $search
     * @param array $select
     * @return Builder
     */
    public function scopeSearch(Builder $query, $search = [] , $select = ['*'])
    {
        $query = $query->select($select);
        if ($search['id']) {
            $query = $query->where('id',$search['id']);
        }
        if ($search['withdraw_sn']) {
            $query = $query->where('withdraw_sn',$search['withdraw_sn']);
        }
        if ($search['out_batch_no']) {
            $query = $query->where('out_batch_no',$search['out_batch_no']);
        }
        if ($search['member_id']) {
            $query = $query->where('member_id',$search['member_id']);
        }
        if (isset($search['status']) && is_numeric($search['status'])) {
            $query = $query->where('status',$search['status']);
        }
        if ($search['many_status']) {
            $query = $query->whereIn('status',$search['many_status']);
        }
        if (isset($search['type']) && is_numeric($search['type'])) {
            $query = $query->where('type',$search['type']);
        }
        return $query;
    }
}