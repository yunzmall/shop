<?php

namespace app\backend\modules\user\observers;

use app\backend\modules\user\services\PermissionService;
use app\common\exceptions\ShopException;
use app\common\models\user\UniAccountUser;
use app\common\models\user\UserProfile;
use app\common\models\user\YzPermission;
use app\common\models\user\YzUserRole;
use app\common\observers\BaseObserver;
use Illuminate\Database\Eloquent\Model;

class UserObserver extends BaseObserver
{
    public function created(Model $model)
    {
        //框架操作员属性写入，只有创建后才会写入，商城不支持修改
        $uniAccountUserModel = new UniAccountUser();

        $accountData = array(
            'uid'     => $model->uid,
            'role'    => 'operator',
            'rank'    => '0',
            'uniacid' => \YunShop::app()->uniacid
        );
        $uniAccountUserModel->fill($accountData);
        if (!$uniAccountUserModel->save()) {
            throw new ShopException('操作员user写入失败,请重试！');
        }
    }

    public function deleted(Model $model)
    {
        YzUserRole::where('user_id', $model->uid)->delete();
    }

    public function saving(Model $model)
    {
        //验证操作员简介数据
        $profileModel = UserProfile::getProfileByUid($model->uid) ?: new UserProfile();

        $profileModel->setRawAttributes($model->widgets['profile']);
        $validator = $profileModel->validator();
        if ($validator->fails()) {
            throw new ShopException('姓名为空或手机号格式错误');
        }
    }

    public function saved(Model $model)
    {
        //操作员简介写入 或 修改操作员简介
        $profileModel = UserProfile::getProfileByUid($model->uid) ?: new UserProfile();

        $profileModel->fill($model->widgets['profile']);
        $profileModel->uid = $model->uid;
        if (config('app.framework') != 'platform') {
            $profileModel->createtime = time();
        }
        if (!$profileModel->save()) {
            throw new ShopException('操作员简介写入失败,请重试！！');
        }

        //操作员角色写入 或 修改 没有主键的表，删除原数据重新添加
        YzUserRole::removeDataByUserId($model->uid);

        $yzUserRoleModel = new YzUserRole();

        $yzUserRoleModel->user_id = $model->uid;
        $yzUserRoleModel->role_id = $model->widgets['role_id'] ?: '0';
        if (!$yzUserRoleModel->save()) {
            throw new ShopException('操作员角色关联写入失败,请重试！！');
        }

        //操作员权限写入 或 修改， 修改时需注意：挂件中的 permission 需要去除角色权限
        //同时，目前采用删除操作员原权限，写入新权限做法
        YzPermission::deleteUserPermissionByUserId($model->uid);

        if ($model->widgets['perms']) {
            $permissions = (new PermissionService())->addedToPermission($model->widgets['perms'], YzPermission::TYPE_USER, $model->uid);
            if (!YzPermission::insertYzPermission($permissions)) {
                throw new ShopException('写入操作员权限失败，请重新编辑！！');
            }
        }
    }
}
