<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/10
 * Time: 下午5:47
 */

namespace app\backend\modules\finance\models;


class PointLog extends \app\common\models\finance\PointLog
{
    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearch($query, $search = [])
    {
        if ($search['source']) {
            $query->where("point_mode", $search['source']);
        }
        if ($search['income_type']) {
            $query->where("point_income_type", $search['income_type']);
        }
        if ((is_numeric($search['time']['start']) && $search['time']['start'] > 0) && is_numeric($search['time']['end']) && $search['time']['end'] > 0) {
            $query->whereBetween('created_at', [$search['time']['start'], $search['time']['end']]);
        }
        $query->searchMember($search);
    }
}