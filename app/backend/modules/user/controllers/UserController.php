<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 07/03/2017
 * Time: 16:13
 */

namespace app\backend\modules\user\controllers;


use app\backend\modules\user\services\PermissionService;
use app\common\components\BaseController;
use app\common\helpers\Cache;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\user\User;
use app\common\models\user\YzRole;
use app\common\services\Utils;
use app\common\models\user\YzUserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    const PageSize = 10;

    /*
     *  操作员分页列表
     **/

    public function index()
    {
        if (request()->ajax()) {
            $records = User::records();

            $search = request()->search;
            if ($search) {
                $records = $records->search($search);
            }

            if (config('app.framework') == 'platform') {
                $userList = $records->orderBy('created_at', 'desc')->paginate(static::PageSize);
            } else {
                $userList = $records->orderBy('starttime', 'desc')->paginate(static::PageSize);
            }

            $roleList = YzRole::getRoleListToUser();
            return $this->successJson('请求接口', [
                'roleList' => $roleList,
                'userList' => $userList,
            ]);

        }

        return view('user.user.user')->render();

    }

    public function switchUser()
    {
        $id = request()->uid;
        $user = User::where('uid', $id)->first();
        if ($user) {
            $dispatcher = User::getEventDispatcher();

            User::unsetEventDispatcher();  //临时禁用观察者
            switch ($user->status) {
                case User::ROLE_ENABLE:
                    $user->status = User::ROLE_DISABLE;
                    if ($user->save()) {
                        User::setEventDispatcher($dispatcher);
                        return $this->successJson('角色禁用成功');
                    }
                    break;

                case User::ROLE_DISABLE:
                    $user->status = User::ROLE_ENABLE;
                    if ($user->save()) {
                        User::setEventDispatcher($dispatcher);
                        return $this->successJson('角色启用成功');
                    }
                    break;
            }
            return $this->errorJson('数据出错，保存失败');
        } else {
            return $this->errorJson('找不到该操作员，请重试');
        }
    }

    /*
     *  添加操作员
     **/


    public function store()
    {
        $userModel = new User();
        $requestUser = request()->user;
        if ($requestUser) {
            $requestUser['username'] = trim($requestUser['username']);
            $userData = $this->addedUserData($requestUser);

            if (config('app.framework') == 'platform') {
                $userData['owner_uid'] = 0;
            }

            $userModel->fill($userData);
            $userModel->widgets = request()->widgets;
            $userModel->widgets['perms'] = request()->perms;

            $validator = $userModel->validator();
            if ($validator->fails()) {
                return $this->errorJson($validator->messages());
            } else {
                $verifyPassword = verifyPasswordStrength($userModel->password);
                if($verifyPassword !== true){
                    return $this->errorJson($verifyPassword);
                }

                if (config('app.framework') == 'platform') {
                    $userModel->password = bcrypt($userModel->password);
                } else {
                    $userModel->password = $this->password($userModel->password, $userModel->salt);
                }

                if ($userModel->save()) {
                    Cache::flush();
                    return $this->successJson('添加操作员成功.', Url::absoluteWeb('user.user.index'));
                } else {
                    return $this->errorJson('请检查手机号或电话格式');
                }
            }

        }
        $permissions = PermissionService::getPermission();
        $permissions = PermissionService::getApiData($permissions);
        $roleList = YzRole::getRoleListToUser();
        if (request()->ajax()) {
            return $this->successJson('请求接口', [
                'roleList'    => $roleList,
                'permissions' => $permissions,
            ]);
        }

        return view('user.user.form')->render();
    }

    /*
     *  修改操作员
     **/


    public function update()
    {
        $id = request()->id;
        $userModel = User::getUserByid($id);

        if (!$userModel) {
            return $this->errorJson("未找到数据或已删除！");
        }
        $permissionService = new PermissionService();

        $userPermissions = $permissionService->handlePermission($userModel->permissions->toArray());

        $permissions = PermissionService::getPermission();
        $roleList = YzRole::getRoleListToUser();

        $rolePermissions = [];
        if ($userModel->userRole && $userModel->userRole->role) {
            $rolePermissions = $permissionService->handlePermission($userModel->userRole->permissions->toArray());
            $userPermissions = array_merge($rolePermissions, $userPermissions);
            //dd($permissionService->handlePermission($userModel->userRole->permissions->toArray()));
        }
        //dd($userPermissions);
        //修改 start
        $requestUser = request()->user;
        if ($requestUser) {
            //dd(\YunShop::request());
            $userModel->status = $requestUser['status'];
            if ($requestUser['password']) {
                $verifyPassword = verifyPasswordStrength($requestUser['password']);
                if($verifyPassword !== true){
                    return $this->errorJson($verifyPassword);
                }
                $userModel->password = user_hash($requestUser['password'], $userModel->salt);
            }
            $userModel->widgets = request()->widgets;
            $userModel->widgets['perms'] = request()->perms;
            if ($userModel->save()) {
                Cache::flush();
//                $key = 'user.permissions.'.$userModel->uid;
//                \Cache::forget($key);
//                \Cache::forget('menu_list'.$userModel->uid);
                return $this->successJson('修改操作员成功.', Url::absoluteWeb('user.user.update', array('id' => $userModel->uid)));
            }
        }
        $permissions = PermissionService::getApiData($permissions);
        if ($userModel->userRole->role_id == 0) {
            $userModel = $userModel->toArray();
            $userModel['user_role']['role_id'] = '';
        }
        return view('user.user.edit', [
            'user'            => $userModel,
            'roleList'        => $roleList,
            'permissions'     => $permissions,
            'rolePermission'  => $rolePermissions,
            'userPermissions' => $userPermissions
        ])->render();
    }

    /**
     * 删除操作员
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy()
    {
        $userModel = User::find(request()->id);

        if (!$userModel) return $this->errorJson("记录不存在或已删除！");

        if (!$userModel->delete()) return $this->errorJson('删除失败，请重试!');

        $this->debugLog();

        return $this->successJson("删除操作员成功。", Url::absoluteWeb('user.user.index'));
    }

    /**
     * 获取当前登录用户信息
     */
    public function getAdminUserInfo()
    {
        //获取当前登录用户的账号
        $array = [];
        $array['uid'] = \YunShop::app()->uid;
        $array['uniacid'] = \YunShop::app()->uniacid;
        $array['acid'] = \YunShop::app()->acid;
        $array['username'] = \YunShop::app()->username;

        //获取当前登录用户的手机号
        $array['mobile'] = DB::table('yz_users_profile')->where('uid',$array['uid'])->value('mobile');

        return $this->successJson("获取成功", $array);
    }

    /**
     * 修改用户登录密码
     */
    public function resetPassword()
    {
        $old_password = request()->old_password;
        $new_pass = request()->new_pass;
        $username = request()->username;
 
        $userModel = User::where('uid',\YunShop::app()->uid)->first();

        if (!$userModel) 
        {
            return $this->errorJson('用户不存在');
        }

        if (!Hash::check($old_password, $userModel->password)) 
        {
            return $this->errorJson('原密码错误');
        }

        //平台的验证统一使用 validatePassword方法
        $verifyPassword = validatePassword($new_pass);
        if($verifyPassword !== true){
            return $this->errorJson($verifyPassword);
        }
        //密码加密
        if (config('app.framework') == 'platform') 
        {
            $new_pass = bcrypt($new_pass);
        } else {
            $new_pass = $this->password($old_pass, $userModel->salt);
        }

        $data = [];
        $data['password'] = $new_pass;
        $res = User::where('uid', $userModel->uid)->update($data);
        return $this->successJson("修改成功");
    }

    /**
     * 用户被删除BUG-log
     */
    private function debugLog()
    {
        $find = base_path() . '\storage\logs\user_admin_delete_log.log';
        if (!file_exists($find)) {
            fopen($find, 'a');
        }
        $array = [];
        $array['deleteid'] = request()->id;
        $array['uid'] = \YunShop::app()->uid;
        $array['uniacid'] = \YunShop::app()->uniacid;
        $array['acid'] = \YunShop::app()->acid;
        $array['username'] = \YunShop::app()->username;
        $array['siteurl'] = \YunShop::app()->siteurl;
        $array['time'] = date('Y-m-d H:i:s', time());
        $txt = "app\backend\modules\user\controllers\UserController.php\n";
        $txt .= json_encode($array, true) . "\n\n";
        file_put_contents($find, $txt, FILE_APPEND);
        \Log::debug("====用户被删除BUG-log===", $array);
    }

    /**
     * 附加的用户数据
     * @param string $data 需要储存的数据
     * @return string
     */
    private function addedUserData(array $data = [])
    {
        if (config('app.framework') == 'platform') {
            $data['lastvisit'] = time();
            $data['lastip'] = Utils::getClientIp();
            $data['joinip'] = Utils::getClientIp();
            $data['salt'] = randNum(8);
        } else {
            $data['joindate'] = $data['lastvisit'] = $data['starttime'] = time();
            $data['lastip'] = CLIENT_IP;
            $data['joinip'] = CLIENT_IP;
            $data['salt'] = $this->randNum(8);
        }

        return $data;
    }

    /**
     * 计算用户密码
     * @param string $passwordinput 输入字符串
     * @param string $salt 附加字符串
     * @return string
     */
    private function password($passwordinput, $salt)
    {
        $authkey = \YunShop::app()->config['setting']['authkey'];
        $passwordinput = "{$passwordinput}-{$salt}-{$authkey}";
        return sha1($passwordinput);
    }

    /**
     * 获取随机字符串
     * @param number $length 字符串长度
     * @param boolean $numeric 是否为纯数字
     * @return string
     */
    private function randNum($length, $numeric = FALSE)
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

}
