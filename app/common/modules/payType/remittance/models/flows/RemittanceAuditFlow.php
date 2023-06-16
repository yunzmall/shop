<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/6/14
 * Time: 下午5:31
 */

namespace app\common\modules\payType\remittance\models\flows;


use app\common\helpers\Cache;
use app\common\models\Flow;
use app\common\modules\payType\remittance\models\process\RemittanceAuditProcess;

class RemittanceAuditFlow extends Flow
{
    const CODE = 'remittanceAudit';
    const STATE_WAIT_AUDIT = 'waitAudit';

    protected static function boot()
    {
        parent::boot();
        self::addGlobalScope(function ($query) {
            $query->where('code',self::CODE);
        });
    }

    public static function getCount()
    {
        if(Cache::has('remittance_audit_count')){
            $count = Cache::get('remittance_audit_count');
        }else{
            $remittanceAuditFlow = self::first();
            $count = RemittanceAuditProcess::where('flow_id', $remittanceAuditFlow->id)->uniacid()
                ->where('status_id',6)
                ->count();
            Cache::put('remittance_audit_count',$count,0.2);
        }
        return $count;

    }
}