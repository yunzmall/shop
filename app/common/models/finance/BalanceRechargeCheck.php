<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/15
 * Time: 11:15
 */

namespace app\common\models\finance;

use app\common\models\BaseModel;
use app\common\models\Member;
use app\common\models\user\User;
use app\framework\Database\Eloquent\Builder;

/**
 * Class BalanceRechargeCheck
 * @package app\common\models\finance
 * @property int uniacid
 * @property int member_id
 * @property float money
 * @property int type
 * @property int operator_id
 * @property int operator
 * @property int source
 * @property string enclosure
 * @property string remark
 * @property string recharge_remark
 * @property string explain
 * @property int status
 * @method $this search(array $searchParam)
 */
class BalanceRechargeCheck extends BaseModel
{
    public $table = 'yz_balance_recharge_check';

    protected $guarded= [''];

    protected $appends = ['status_name','enclosure_src'];

    public function member()
    {
        return $this->hasOne(Member::class,'uid','member_id');
    }

    public function adminUser()
    {
        return $this->hasOne(User::class,'uid','operator_id');
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 0:
                return '待审核';
            case 1:
                return '已通过';
            case 2:
                return '已驳回';
        }
    }

    public function getEnclosureSrcAttribute()
    {
        return $this->enclosure ? yz_tomedia($this->enclosure) : '';
    }

    public function scopeSearch(Builder $query,$search = [])
    {
        if ($search['member_id']) {
            $query->where('member_id',$search['member_id']);
        }
        if ($search['member']) {
            $query->whereHas('member',function ($member) use ($search) {
                $member->where('nickname','like','%'.$search['member'].'%')
                    ->orWhere('realname','like','%'.$search['member'].'%')
                    ->orWhere('mobile',$search['member']);
            });
        }
        if ($search['member_id']) {
            $query->where('member_id',$search['member_id']);
        }
        if ($search['start_time'] && $search['end_time']) {
            $query->whereBetween('created_at',[strtotime($search['start_time']),strtotime($search['end_time'])]);
        }
        if (isset($search['status']) && is_numeric($search['status'])) {
            $query->where('status',$search['status']);
        }
        return $query;
    }
}