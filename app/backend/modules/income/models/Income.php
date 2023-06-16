<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/5/15 上午10:00
 * Email: livsyitian@163.com
 */

namespace app\backend\modules\income\models;


use app\common\scopes\UniacidScope;

class Income extends \app\common\models\Income
{
    public static function boot()
    {
        parent::boot();
        self::addGlobalScope( new UniacidScope);
    }


    public function scopeRecords($query)
    {
        return $query;
    }


    public function scopeWithMember($query)
    {
        return $query->with(['member' => function($query) {
            return $query->select('uid', 'nickname', 'realname', 'avatar', 'mobile');
        }]);
    }


    public function scopeSearch($query, $search)
    {
        if ($search['class']) {
            //门店预约中途换了关联模型,兼容新旧记录
            if($search['class']=='Yunshop\Appointment\common\models\AppointmentOrderService'){
                $query->whereIn('incometable_type', [$search['class'],'Yunshop\Appointment\common\models\AppointmentIncome']);
            }else{
                $query->where('incometable_type', $search['class']);
            }
        }
        if ($search['status'] || $search['status'] == '0') {
            $query->where('status', $search['status']);
        }
        if ($search['pay_status'] || $search['pay_status'] == '0') {
            $query->where('pay_status', $search['pay_status']);
        }
        if (Is_numeric($search['time']['start']) && Is_numeric($search['time']['end'])) {
            $query = $query->whereBetween('created_at', [$search['time']['start'] / 1000, $search['time']['end'] / 1000]);
        }
        return $query;
    }


    public function scopeSearchMember($query, $search)
    {
        if ($search['member_id'] || $search['realname']) {
            $query->whereHas('member', function($query)use($search) {
                if ($search['realname']) {
                    $query->select('uid', 'nickname','realname','mobile','avatar')
                        ->where('realname', 'like', '%' . $search['realname'] . '%')
                        ->orWhere('mobile', 'like', '%' . $search['realname'] . '%')
                        ->orWhere('nickname', 'like', '%' . $search['realname'] . '%');
                }
                if ($search['member_id']) {
                    $query->whereUid($search['member_id']);
                }
            });
        }
        return $query;
    }

}
