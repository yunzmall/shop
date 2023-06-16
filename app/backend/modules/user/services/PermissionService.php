<?php

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/17
 * Time: 下午7:03
 */
namespace app\backend\modules\user\services;
class PermissionService
{
    /*
     *  添加权限所属类型及所属人ID到权限数组$data
     *
     *  @parms array $data
     *
     *  @return array
     **/
    public function addedToPermission($data =[], $type, $itemId)
    {
        foreach ($data as $key => $value) {
            $permissions[] = array(
                'type' => $type,
                'item_id' => $itemId,
                'permission' => $value
            );
        }
        return $permissions;
    }

    /*
     *  处理权限数组：读取权限数组，返回一维权限数组$permissions
     *
     *  @parms array $data
     *
     *  @return array
     **/
    public function handlePermission($data)
    {
        if (!is_array($data)) {
            return $permissions = [];
        }
        $permissions = [];
        foreach ($data as $key => $value) {
            if ($key['permission']) {
                $permissions[] = $key['permission'];
            }
            if ($value['permission']) {
                $permissions[] = $value['permission'];
            }
        }
        return $permissions;
    }


    //test

    public static function getPermission()
    {
        $permissions = \app\backend\modules\menu\Menu::current()->getItems();

        //一级循环
        foreach ($permissions as $keyOne => &$permissionOne) {
            if (isset($permissionOne['child']) && $permissionOne['child']) {
                $permissionOne['child'] = static::getAllChild($permissionOne['child']);
            }
        }
        return $permissions;
    }

    public static function getAllChild($permissions)
    {
        foreach ($permissions as $keyTwo => &$permissionTwo) {

            if (isset($permissionTwo['child']) && $permissionTwo['child']) {
                //三级循环
                foreach ($permissionTwo['child'] as $keyThree => $permissionThree) {
                    //dump($keyThree);
                    //dump($permissionThree);
                    //如果三级有子集的提出，改为和二级同级别
                    if (isset($permissionThree['child']) && $permissionThree['child']) {
                        //$permissionThree['']
                        $permissions[$keyThree] = $permissionThree;
                        unset($permissionTwo['child'][$keyThree]);

                        $permissions = static::getAllChild($permissions);
                    }
                }
            }
        }
        return $permissions;
    }

    //按照前端要求的数据格式返回，去掉key，过滤没用数据
    public static function  getApiData($permissions )
    {
        foreach ($permissions as $keyOne => &$valueOne){

            if(!isset($valueOne['permit']) || $valueOne['permit'] === 0 ){
                unset($permissions[$keyOne]);
                unset($valueOne);
            }else{
                $permissions[$keyOne]['key_name'] = $keyOne;
            }

            foreach ($valueOne['child'] as $keyTwo => $valueTwo){
                if(!isset($valueTwo['permit']) || $valueTwo['permit'] === 0 || in_array($keyTwo, \app\common\services\PermissionService::founderPermission())){
                    unset($permissions[$keyOne]['child'][$keyTwo]);
                    unset($valueTwo);
                }else{
                    $permissions[$keyOne]['child'][$keyTwo]['key_name'] = $keyTwo;
                }

                foreach ($valueTwo['child'] as $keyThird => $valueThird){
                    if(!isset($valueThird['permit']) || $valueThird['permit'] === 0){
                        unset($permissions[$keyOne]['child'][$keyTwo]['child'][$keyThird]);
                    }else{
                        $permissions[$keyOne]['child'][$keyTwo]['child'][$keyThird]['key_name'] = $keyThird;
                    }
                }
            }
        }
        $permissions = array_values($permissions);
        foreach ($permissions as $k => &$v){
            $permissions[$k]['child'] = array_values($permissions[$k]['child']);
            foreach ($v['child'] as $k1=>&$v1){
                $v1['child'] = array_values($v1['child']);
            }
        }

        return $permissions;
    }
}
