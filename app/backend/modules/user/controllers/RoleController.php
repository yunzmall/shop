<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 07/03/2017
 * Time: 16:13
 */

namespace app\backend\modules\user\controllers;


use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\backend\modules\user\services\PermissionService;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\user\YzPermission;
use app\common\models\user\YzRole;

class RoleController extends UploadVerificationBaseController
{

    //todo 页面功能逻辑优化，搜索功能完善
    /**
     * 角色列表
     */

    public function index()
    {
        if(request()->ajax()){
            $pageSize = '10';
            $search = request()->search;
            $roleList = YzRole::getPageList($pageSize,$search);

            return  $this->successJson('请求接口成功',[
                'roleList'  => $roleList,
                'search'    => $search
            ]);
        }
        return view('user.role.index')->render();
    }

    public function switchRole()
    {
        $id = request()->id;
        $user = YzRole::where('id', $id)->first();
        if($user){
            switch ($user['status'])
            {
                case YzRole::ROLE_ENABLE:
                     $user->status = YzRole::ROLE_DISABLE;
                     if($user->save()){
                         return $this->successJson('角色禁用成功');
                     }
                    break;

                case YzRole::ROLE_DISABLE:
                    $user->status = YzRole::ROLE_ENABLE;
                    if($user->save()){
                      return $this->successJson('角色启用成功');
                    }
                    break;
            }
            return  $this->errorJson('数据出错，保存失败');
        }else{
            return  $this->errorJson('找不到该角色，请重试');
        }
    }




    /**
     * 创建角色
     */

    public function store()
    {
            $roleModel = new YzRole();

            $requestRole = request()->YzRole;
            //dd($requestRole);
            if ($requestRole) {
                //将数据赋值到model
                $roleModel->setRawAttributes($requestRole);
                //其他字段赋值
                $roleModel->uniacid = \YunShop::app()->uniacid;

                //字段检测
                $validator = $roleModel->validator($roleModel->getAttributes());
                //dd($validator->messages());
                if ($validator->fails()) {
                    return  $this->errorJson("角色数据验证出错");

                }else{
                    if ($roleModel->save()) {
                        $requestPermission = request()->perms;
                        //数据处理
                        if ($requestPermission) {
                            //dd(1);
                            $data = [];
                            foreach ($requestPermission as $key => $value) {
                                $data[$key] = array(
                                    'type'      => YzPermission::TYPE_ROLE,
                                    'item_id'   => $roleModel->id,
                                    'permission' => $value
                                );
                                $validator = (new YzPermission)->validator($data);
                                if ($validator->fails()) {
                                    return  $this->errorJson("角色数据验证出错");
                                }
                            }
                            $result = YzPermission::insertYzPermission($data);
                            if (!$result) {
                                //删除刚刚添加的角色
                                YzRole::deleteRole($roleModel->id);
                                return  $this->errorJson("角色数据验证出错");
                            }
                        }
                        return $this->successJson('添加角色成功', Url::absoluteWeb('user.role.index'));
                    } else {
                        $this->errorJson('角色数据写入出错，请重试！');
                    }
                }
            }
            $permissions = PermissionService::getPermission();
           $permissions = PermissionService::getApiData($permissions);
        if(request()->ajax()){
            return $this->successJson('请求接口成功',[
                'role'          => $roleModel,
                'permissions'   =>$permissions,
            ]);
           }

        return view('user.role.store');
    }

    /**
     * 修改角色
     */

    public function update()
    {
            $permissions = PermissionService::getPermission();
            $roleModel = YzRole::getRoleById(request()->id);
            //dd($role);
            $rolePermission = $roleModel->toArray();
            foreach ($rolePermission['role_permission'] as $key) {
                $rolePermissions[] = $key['permission'];
            }
            if(empty($rolePermissions)) {
                $rolePermissions = [];
            }

            $requestRole = request()->YzRole;
            if ($requestRole) {
                $roleModel->setRawAttributes($requestRole);
                $validator = $roleModel->validator($roleModel->getAttributes());
                if ($validator->fails()) {
                    $this->errorJson('权限数据验证失败');
                } else {
                    if ($roleModel->save()) {
                        //return $this->message("更新角色成功");
                        \Cache::flush();
                        $requestPermission = request()->perms;
                        if ($requestPermission) {
                            //dd(1);
                            $data = [];
                            foreach ($requestPermission as $key => $value) {
                                $data[$key] = array(
                                    'type'      => YzPermission::TYPE_ROLE,
                                    'item_id'   => request()->id,
                                    'permission' => $value
                                );
                                $validator = (new YzPermission)->validator($data);
                                if ($validator->fails()) {
                                    $this->errorJson('权限数据验证失败');
                                }
                            }
                            //删除原权限数据，更新数据储存
                            YzPermission::deleteRolePermission(request()->id);
                            $result = YzPermission::insertYzPermission($data);
                            if (!$result) {
                                //删除刚刚添加的角色
                                YzRole::deleteRole($roleModel->id);
                                $this->errorJson('角色更新成功，权限数据写入出错，请重新编辑权限！');
                            } else {
                                return $this->successJson('编辑角色成功', Url::absoluteWeb('user.role.index'));
                            }
                        } else {
                            YzPermission::deleteRolePermission(request()->id);
                        }
                        return $this->successJson('编辑角色成功', Url::absoluteWeb('user.role.index'));

                    }
                }

            }
            $permissions = PermissionService::getApiData($permissions);
            return view('user.role.form',[
                'role'=>$rolePermission,
                'permissions'=>$permissions,
                'userPermissions'=>$rolePermissions
            ])->render();
    }

    /**
     * 删除角色
     */

    /**
     * 删除角色
     */
    public function destory()
    {
        $requestRole = YzRole::getRoleById(request()->id);
        if (!$requestRole) {
            return $this->errorJson('未找到数据或已删除');
        }
        $resultRole = YzRole::deleteRole(request()->id);
        if ($resultRole) {
            $resultPermission = YzPermission::deleteRolePermission(request()->id);
            if ($resultPermission) {
                return $this->successJson('删除角色成功。', Url::absoluteWeb('user.role.index'));
            }
            //是否需要怎么增加角色权限删除失败提示
        } else {
            return $this->errorJson('数据写入出错，请重试！');
        }
    }

}
