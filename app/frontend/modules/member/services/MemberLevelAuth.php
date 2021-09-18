<?php


namespace app\frontend\modules\member\services;


use app\framework\Support\Facades\Log;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberLevel;

class MemberLevelAuth
{
    public $model = null;

    public function __construct()
    {
        $this->model = new MemberModel();
    }

    //在这里做验证权限
    public function doAuth($id, $allow_auth)
    {
        try {
            if (!is_array($allow_auth)) {
                $allow_auth = json_decode($allow_auth, true);
            }
            //如果用户没有设置权限则返回的checkitem数组为空，则所有用户皆可以访问
            if (!$allow_auth) {
                return true;
            }
            // 判断等级是否存在
            $is_exsit = MemberLevel::uniacid()->whereIn('id',$allow_auth)->pluck('id');
            if(!$is_exsit) return true;

            $result = $this->model->getUserInfoByUid($id);
            //如果找不到当前会员信息则返回false，无法访问
            if (!$result) {
                return false;
            }
            $result = $result->toArray();
            $member_level = $result['yz_member']['level_id'];
            //如果checkitem数组中包含all，则证明后台设置了全部等级可以访问
            if (in_array("all", $allow_auth, true)) {
                return true;
            }
            //如果用户等级在checkitem数组中则可以访问
            if (in_array($member_level, $allow_auth)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
    }

}