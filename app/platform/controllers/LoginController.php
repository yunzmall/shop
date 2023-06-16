<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/2/15
 * Time: 下午6:56
 */

namespace app\platform\controllers;


use app\common\services\Utils;
use app\frontend\modules\member\services\MemberService;
use app\platform\modules\system\models\WhiteList;
use app\platform\modules\user\models\OfficialWebsiteContact;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use app\platform\modules\user\models\AdminUser;
use app\platform\modules\system\models\SystemSetting;
use app\platform\modules\application\models\UniacidApp;
use app\platform\modules\application\models\AppUser;
use app\common\helpers\Url;
use app\common\helpers\Cache;
use Illuminate\Support\Facades\DB;

class LoginController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';
    protected $username;
    private $authRole = ['operator', 'clerk'];


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //     $this->middleware('guest:admin', ['except' => 'logout']);
    }

    /**
     * 自定义字段名
     * 可使用
     * @return array
     */
    public function atributeNames()
    {
        return [
            'username' => '用户名',
            'password' => '密码'
        ];
    }

    /**
     * 字段规则
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required',
            'password' => 'required',
        ];
    }

    /**
     * 重写登录视图页面
     * @return [type]                   [description]
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    /**
     * 自定义认证驱动
     * @return [type]                   [description]
     */
    protected function guard()
    {
        return auth()->guard('admin');
    }

    /**
     * 重写验证字段.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $this->guard('admin')->logout();
        request()->session()->flush();
        request()->session()->regenerate();

        Utils::removeUniacid();

        return $this->successJson('成功', []);
    }

    /**
     * 重写登录接口
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->offsetSet('password',base64_decode($request->password));
        try {
            $this->validate($this->rules(), $request, [], $this->atributeNames());
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
        $loginset = SystemSetting::settingLoad('loginset', 'system_loginset');
        if($loginset['pic_verify'] == 1)
        {
            if (!$request->captcha || app('captcha')->platformCheck($request->captcha) == false)
            {
                return $this->errorJson('图形验证码错误');
            }
        }

        if ($loginset['sms_verify'] == 1) {
            $user = AdminUser::where('username',$request->username)->with('hasOneProfile')->first();
            if(!$user || !\YunShop::request()->mobile)
            {
                return $this->errorJson('手机号填写错误');
            }

            //店员用户手机号码验证
            if ($user->hasOneProfile->mobile != \YunShop::request()->mobile && $user->type == 3)
            {
                // 区域分红（区域代理商）、酒店（hotel）、供应商、自提点（package-deliver）、分公司（subsidiary）,门店
                $mobile = $this->getUserType($user->uid);
                if ($mobile == '' || $mobile != \YunShop::request()->mobile) {
                    return $this->errorJson('手机号填写错误');
                }
            }elseif ($user->hasOneProfile->mobile != \YunShop::request()->mobile) {
                return $this->errorJson('手机号填写错误');
            }

            //检查验证码是否正确

            $check_code = app('sms')->checkAppCode($request->mobile, $request->code);

            if ($check_code['status'] != 1) {
                return $this->errorJson($check_code['json']);
            }
        }

        if($loginset['white_list_verify'] == 1 && !WhiteList::isWhite($request->getClientIp())) {
            //白名单检测，总账号（uid=1）不限制
            !isset($user) && $user = AdminUser::where('username',$request->username)->first();
            if (!$user || $user['uid'] != 1) {//没查到用户也提示这个
                return $this->errorJson('ip不在白名单内');
            }
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $limitLogin = $this->limiter()->tooManyAttempts(
            $this->throttleKey($request), $loginset->login_limit_num?:5, $loginset->login_limit_time?:30
        );
        if ($limitLogin) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request,$loginset['login_expire_num'])) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * 重写登录成功json返回
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $loginToken = rand(1000, 9999).time();
        session(['login_token'=>$loginToken]);
        $this->clearLoginAttempts($request);

        $admin_user = AdminUser::where('uid', $this->guard()->user()->uid);
        $admin_user->update([
            'lastvisit' =>  time(),
            'lastip' => Utils::getClientIp(),
            'login_token' => $loginToken
        ]);

        $hasOneAppUser = $admin_user->first()->hasOneAppUser;
        if ($hasOneAppUser->role == 'clerk' || $hasOneAppUser->role == 'operator') {
            $sys_app = UniacidApp::getApplicationByid($hasOneAppUser->uniacid);
            if (!is_null($sys_app->deleted_at)) {
                return $this->successJson('平台已停用', ['status' => -5]);
            } elseif ($sys_app->validity_time !=0 && $sys_app->validity_time < mktime(0,0,0, date('m'), date('d'), date('Y'))) {
                return $this->successJson('平台已过期', ['status' => -5]);
            }
        }

		$loginset = SystemSetting::settingLoad('loginset', 'system_loginset');
		$pwd_remind = 0;
		$msg = '';
		//密码强度校验
		if ($loginset['password_verify'] == 1) {
            $validatePassword = validatePassword($request->password);
			if ($validatePassword !== true) {
				$pwd_remind = 2;
				$msg = '您当前密码过于简单，请先修改密码';
			}
		}
        if ($this->guard()->user()->uid !== 1) {

            $user = $this->guard()->user();
            if($loginset['password_change'] == 1){
                if(!$user->change_password_at)
                {
                    $user->change_password_at = time();
                    $user->save();
                    return $this->successJson('成功', ['pwd_remind' => 1,'msg'=>'首次登陆，建议您修改密码']);
                }
            }
            if($user->change_password_at+6912000 <= time() && $user->change_remind == 0 && $loginset['force_change_pwd'] == 1)
            {
                $user->change_remind = 1;
                $user->save();
                return $this->successJson('成功', ['pwd_remind' => 1,'msg'=>'您已长时间未修改密码，请您先修改密码，否则账号将在10天后冻结']);
            }

            $account = AppUser::getAccount($this->guard()->user()->uid);

            if (!is_null($account) && in_array($account->role, $this->authRole)) {
                Utils::addUniacid($account->uniacidb);

                \YunShop::app()->uniacid = $account->uniacid;

                self::addLogsAction($this->guard()->user()->uid,'其他用户',2);

                return $this->successJson('成功', ['url' => Url::absoluteWeb('index.index', ['uniacid' => $account->uniacid,'pwd_remind'=>$pwd_remind])]);
            }
        }

        self::addLogsAction($this->guard()->user()->uid,'超级管理员',1);

        return $this->successJson('成功',['pwd_remind'=>$pwd_remind,'msg'=>$msg]);
    }

    /**
     * 重写登录失败json返回
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendFailedLoginResponse(Request $request)
    {
        return $this->errorJson(Lang::get('auth.failed'), []);
    }

    /**
     * 重写登录失败次数限制
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $message = Lang::get('auth.throttle', ['seconds' => $seconds]);

        return $this->errorJson($message);
    }

    /**
     * Attempt to log the user into the application.
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request,$minutes = 0)
    {
        $result = $this->guard()->attempt(
            $this->credentials($request),1
        );
        if ($result) {
            $minutes = $minutes ?? 2628000;
            $user = $this->guard()->getuser();
            $value = $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword();
            $this->guard()->getCookieJar()->queue($this->guard()->getRecallerName(),$value,$minutes);
        }
        return $result;
    }

    public function site()
    {
        $default = [
            'name' => "商城管理系统",
            'site_logo' => yz_tomedia("/static/images/site_logo.png"),
            'title_icon' => yz_tomedia("/static/images/title_icon.png"),
            'advertisement' => yz_tomedia("/static/images/advertisement.jpg"),
            ];

        $copyright = SystemSetting::settingLoad('copyright', 'system_copyright');

        $copyright['name'] = !isset($copyright['name']) ? $default['name'] : $copyright['name'];
        $copyright['site_logo'] = !isset($copyright['site_logo']) ? $default['site_logo'] : $copyright['site_logo'];
        $copyright['title_icon'] = !isset($copyright['title_icon']) ? $default['title_icon'] : $copyright['title_icon'];
        $copyright['advertisement'] = !isset($copyright['advertisement']) ? $default['advertisement'] : $copyright['advertisement'];
        $copyright['information'] = !isset($copyright['information']) ? $default['information'] : $copyright['information'];

        $loginset = SystemSetting::settingLoad('loginset', 'system_loginset');

        $copyright['register_open'] = $loginset['register_open'] ?: 0;

        if($loginset['pic_verify'] == 1)
        {
            $copyright['captcha']['status'] = true;
            $captcha = app('captcha');
            $captcha_base64 = $captcha->create('default', true,true);
            $copyright['captcha']['img'] = $captcha_base64['img'];
        }else{
            $copyright['captcha']['status'] = false;
        }
        if($loginset['sms_verify'] == 1)
        {
            $copyright['sms']['status'] = true;
        }else{
            $copyright['sms']['status'] = false;
        }

        if ($copyright) {
            return $this->successJson('成功', $copyright);
        } else {
            return $this->errorJson('没有检测到数据', '');
        }
    }

    public function loginCode()
    {
        $mobile = request()->mobile;
        $state = \YunShop::request()->state ? : '86';
        if(!request()->username)
        {
            return $this->errorJson('请先完善账号信息');
        }
        $user = AdminUser::where('username',request()->username)->with('hasOneProfile')->first();
        if(!$user || !$mobile)
        {
            return $this->errorJson('手机号填写错误');
        }

        if ($user->hasOneProfile->mobile != $mobile) 

        {
            if ($user->type != 3)
            {
                return $this->errorJson('手机号填写错误');
            }elseif ($this->getUserType($user->uid) != $mobile) {
                return $this->errorJson('手机号填写错误');
            }
        }


        $code = rand(1000, 9999);

        Cache::put($mobile.'_code', $code, 60 * 10);

        return (new ResetpwdController())->sendSmsV2($mobile, $code, $state,'login',3);
    }

    public function refreshPic()
    {
        $captcha = app('captcha');
        $captcha_base64 = $captcha->create('default', true,true);
        return $this->successJson('成功', $captcha_base64['img']);
    }

    //添加登录记录
    public static function addLogsAction($userId, $detail_remark, $admin_type)
    {
        $detail_parameter = array(2, 0, $detail_remark, 0);
        list($other, $member_id, $remark, $uniacid) = $detail_parameter;//依次：其他管理员类型，默认会员ID，默认类型

        $username = DB::table('yz_admin_users')->where('uid', $userId)->value('username');

        //其他管理员
        if ($admin_type == $other) {
            $userTable = 'uni_account_users';
            if (config('app.framework') == 'platform') {
                $userTable = 'yz_app_user';
            }

            //其他管理员所属公众号
            $uniacid = DB::table($userTable)->where('uid', $userId)->value('uniacid');

            $uid_arr = ['yz_supplier'];//uid
            $user_id_arr = ['yz_user_role', 'yz_area_dividend_agent'];//user_id
            $user_uid_arr = ['yz_package_deliver', 'yz_subsidiary', 'yz_store', 'yz_hotel'];//user_uid

            $all_arr = array_merge($uid_arr, $user_id_arr, $user_uid_arr);

            //区分表的账号类型和会员id字段
            $admin_type = [
                'yz_user_role' => ['remark' => '操作员', 'user_type' => ''],
                'yz_supplier' => ['remark' => '供应商', 'user_type' => 'member_id'],
                'yz_store' => ['remark' => '门店', 'user_type' => 'uid'],
                'yz_hotel' => ['remark' => '酒店', 'user_type' => 'uid'],
                'yz_area_dividend_agent' => ['remark' => '区域代理', 'user_type' => 'member_id'],
                'yz_package_deliver' => ['remark' => '自提点', 'user_type' => 'uid'],
                'yz_subsidiary' => ['remark' => '分公司', 'user_type' => 'uid'],
            ];

            $column = '';
            foreach ($all_arr as $item) {
                if ($item == $uid_arr[0]) {
                    $column = 'uid';
                } elseif (in_array($item, $user_id_arr)) {
                    $column = 'user_id';
                } elseif (in_array($item, $user_uid_arr)) {
                    $column = 'user_uid';
                }

                if ($column && \Schema::hasTable($item)) {
                    $hasAdmin = DB::table($item)->where($column, $userId)->first();
                    if ($hasAdmin) {
                        $remark = $admin_type[$item]['remark'];
                        $user_type = $admin_type[$item]['user_type'];
                        $member_id = $hasAdmin[$user_type] ?: 0;
                    }
                }
            }
        }

        //添加登录记录
        DB::table('yz_admin_logs')->insert([
            'uniacid' => $uniacid,
            'admin_uid' => $userId ?: 0,
            'remark' => $remark,
            'ip' => Utils::getClientIp() ?: 0,
            'member_id' => $member_id,
            'username' => $username,
            'created_at' => time(),
            'updated_at' => time()
        ]);
    }

    /**
     * 获取用户身份类型（区域分红（区域代理商）、酒店（hotel）、供应商、自提点（package-deliver）、分公司（subsidiary）,门店）
     */
    public function getUserType($userId)
    {
        $mobile = '';

        if (\Schema::hasTable('yz_store'))

        {
            $member_id = DB::table('yz_store')->where('user_uid',$userId)->value('uid'); //门店
            $mobile = DB::table('yz_store_apply')->where('uid',$member_id)->value('mobile'); //门店
        }

        if (\Schema::hasTable('yz_hotel') && !$mobile) {
            $mobile = DB::table('yz_hotel')->where('user_uid',$userId)->value('mobile');       //酒店
        }

        if (\Schema::hasTable('yz_area_dividend_agent') && !$mobile) {
            $mobile = DB::table('yz_area_dividend_agent')->where('user_id',$userId)->value('mobile');       //区域分红
        }

        if (\Schema::hasTable('yz_supplier') && !$mobile) {
            $mobile = DB::table('yz_supplier')->where('uid',$userId)->value('mobile');       //供应商
        }

        if (\Schema::hasTable('yz_package_deliver') && !$mobile) {
            $mobile = DB::table('yz_package_deliver')->where('user_uid',$userId)->value('deliver_mobile');       //自提点
        }

        if (\Schema::hasTable('yz_subsidiary') && !$mobile) {
            $mobile = DB::table('yz_subsidiary')->where('user_uid',$userId)->value('mobile');       //分公司
        }

        return $mobile;
    }
}