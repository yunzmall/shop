<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-07-02
 * Time: 17:17
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\common\models\member;


use app\common\models\BaseModel;

class MemberChangeLog extends BaseModel
{
    public $table = 'yz_member_change_log';
    public $guarded = [''];
    public $timestamps = true;

    public static function searchLog($search)
    {
        $model = self::select(['id', 'member_id', 'member_id_after', 'created_at'])->uniacid();

        if ($search['member_id']) {
            $model->where('member_id', $search['member_id']);
        }

        if ($search['mark_member_id']) {
            $model->where('member_id_after', $search['mark_member_id']);
        }

        return $model;
    }
}