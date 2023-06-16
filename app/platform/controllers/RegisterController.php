<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/2/20
 * Time: 上午11:09
 */

namespace app\platform\controllers;

use app\common\components\BaseController;
use app\common\exceptions\AdminException;
use app\common\helpers\Cache;
use app\platform\modules\system\models\SystemSetting;
use app\platform\modules\user\models\AdminUser;
use app\platform\modules\user\models\OfficialWebsiteContact;
use app\platform\modules\user\models\OfficialWebsiteTheme;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use app\platform\modules\user\models\YzUserProfile;
use Illuminate\Validation\Rule;
use Yunshop\Article\models\Article;


class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
         register as traitregister;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
     //   $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'username' => 'required|max:255|unique:yz_admin_users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * 重定义注册页面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('admin.auth.register');
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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user_model = new AdminUser;
        $user_model->fill([
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
        ]);
        dump($user_model->save());
        return $user_model;
    }

    public function register(Request $request)
    {
        try {
            $this->traitregister($request);
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        $data = [
            'mobile' => request()->profile['mobile'],
            'uid' => \Auth::guard('admin')->user()->uid
        ];
        $profile_model = new YzUserProfile;
        $profile_model->fill($data);
        $validator = $profile_model->validator();
        if ($validator->fails()) {
            return $this->errorJson($validator->messages());
        } else {
            $profile_model->save();
        }
    }

    //独立框架官网提交电话信息
    public function contactInformation()
    {
        $name = request()->name;
        $name = empty($name) ? "" : $name;
        $mobile = request()->mobile;
        $mobile = empty($mobile) ? "" : $mobile;

        if (empty($name) || empty($mobile)) {
            return $this->errorJson("数据为空");
        }

        $data = [
            "nickname" => $name,
            "mobile" => $mobile,
            "created_at" => time(),
            "updated_at" => time()
        ];

        OfficialWebsiteContact::insert($data);

        return $this->successJson("成功");
    }

    public function followArticle()
    {

        $page = request()->page;
        $page = empty($page) ? 0 : $page;

        $theme = OfficialWebsiteTheme::where("is_default",1)->first();
        $basic = [];
        if ($theme) {
            $basic = $theme->basic ? json_decode($theme->basic,true) : [];
        }

        //获取最新的文章
        $start = $page*3;
        $news = Article::where("state",1)->where("type","!=",1)->orderBy("id","desc")->offset($start)->limit(3);
        if ($basic && $basic['cus_uniacid']) {
            $news->where('uniacid',$basic['cus_uniacid']);
        }
        $news = $news->get();
        $news = empty($news) ? [] : $news->toArray();

        if ($news) {
            foreach ($news as $kk=>$vv) {
                $news[$kk]['detail_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$vv['id'];
                $news[$kk]['thumb'] = yz_tomedia($vv['thumb']);
            }
        }

        //获取虚拟阅读量多的文章
        $read = Article::where("state",1)->where("type","!=",1)->orderBy("virtual_read_num","desc")->offset($start)->limit(3);
        if ($basic && $basic['cus_uniacid']) {
            $read->where('uniacid',$basic['cus_uniacid']);
        }
        $read = $read->get();
        $read = empty($read) ? [] : $read->toArray();

        if ($read) {
            foreach ($read as $kk=>$vv) {
                $read[$kk]['detail_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$vv['id'];
                $read[$kk]['thumb'] = yz_tomedia($vv['thumb']);
            }
        }

        return $this->successJson("",["news"=>$news,"read"=>$read]);

    }

    /**
     * 注册用户
     *
     */
    public function registerAdmin()
    {
        $data = request()->all();
        $mobile_code = Cache::get('app_login_'.$data['mobile']);

        if (AdminUser::where('username' ,$data['username'])->first()){
            return $this->errorJson('该用户名已经存在');
        }

        if (!$data['captcha'] || app('captcha')->platformCheck($data['captcha']) == false) {
            return $this->errorJson('图形验证码错误');
        }

        if (!$mobile_code) {
            return $this->errorJson('手机验证码已过期');
        } elseif ($mobile_code != $data['mobile_code']) {
            return $this->errorJson('手机验证码错误');
        }

        $data['avatar'] = \Setting::get('shop.member')['headimg'] ? : " ";
        $loginset = SystemSetting::settingLoad('loginset', 'system_loginset');

        empty($loginset['endtime']) ? $data['endtime'] = 0 : $data['endtime'] = strtotime("+ {$loginset['endtime']} day");
        empty($loginset['application_number']) ? $data['application_number'] = 0 : $data['application_number'] = $loginset['application_number'];

        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $data['password']) > 0) {
            return $this->errorJson(['密码不能含有中文']);
        }
        if ($loginset['password_verify'] == 1) {
            $validatePassword = validatePassword($data['password']);
            if ($validatePassword !== true) {
                return $this->errorJson($validatePassword);
            }
        }
        if (!$data) {
            return $this->check(AdminUser::returnData('0', AdminUser::PARAM));
        }

        return $this->returnMessage(0, $data);
    }

    public function registerCode()
    {
        $mobile = request()->mobile;

        if (!$mobile) {
            return $this->errorJson('手机号填写错误');
        }

        return (new ResetpwdController())->sendSmsV3($mobile, '86', 4); //只要不等于2或3都会走通用手机验证码发送

    }

    /**
     * 注册页面信息
     */
    public function registerSite()
    {
        $data = [];
        $login_set = SystemSetting::settingLoad('loginset', 'system_loginset');
        $data['agreement_title'] = $login_set['agreement_title'] ?: '';
        $data['agreement_content'] = $login_set['agreement_content'] ?: '';
        $data['password_verify'] = $login_set['password_verify'] ?: 0;
        return $this->successJson('ok', $data);
    }

    /**
     * 刷新注册页面验证码
     */
    public function refreshCaptcha()
    {
        $captcha = app('captcha');
        $captcha_base64 = $captcha->create('default', true, true);
        return $this->successJson('成功', $captcha_base64['img']);
    }


    /**
     * 返回消息
     *
     * @param $sign 1: 修改, 0: 添加
     * @param null $data 参数
     * @param array $user 用户信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnMessage($sign, $data = null, $user = [])
    {
        if ($sign && !$user) {
            return $this->check(AdminUser::returnData('0', AdminUser::NO_DATA));
        }

        $validate = $this->validate($this->rules(), $data, $this->message());

        if ($sign) {
            $validate = $this->validate($this->rules($user), $data, $this->message());
        }

        if ($validate) {
            return $validate;
        }
        $data['password'] = bcrypt($data['password']);
        return $this->check(AdminUser::saveData($data, $user));
    }

    /**
     * 处理表单验证
     *
     * @param array $rules
     * @param \Request|null $request
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Http\JsonResponse
     */
    public function validate($rules,  $request = null, $messages = [], $customAttributes = [])
    {
        if (!isset($request)) {
            $request = request();
        }
        $validator = $this->getValidationFactory()->make($request, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->all());
        }
    }

    /**
     * 表单验证规则
     *
     * @param $user
     * @param $data
     * @return array
     */
    public function rules($user = [], $data = [])
    {
        $rules = [];
        if (request()->path() == "admin/user/create") {
            $rules = [
//                'username' => 'required|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_\-]{3,30}$/u|unique:yz_admin_users',
//                'username' => 'required|unique:yz_admin_users',
                'username' => [
                    'required',
                    Rule::unique('yz_admin_users')->where(function($q){
                        return $q->whereNull('deleted_at');
                    })
                ],
//                'mobile' => 'required|regex:/^1[3456789]\d{9}$/|unique:yz_users_profile',
                'mobile' => [
                    'required',
                    'regex:/^1[3456789]\d{9}$/',
                    Rule::unique('yz_users_profile')->where(function($q){
                        return $q->whereNull('deleted_at');
                    })
                ]
            ];
        }else if(request()->path() == "admin/user/edit") {
            $rules = [
//                'username' => 'required|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_\-]{3,30}$/u|unique:yz_admin_users,username,'.$user['uid'].',uid',
//                'username' => 'required|unique:yz_admin_users,username,'.$user['uid'].',uid',
                'username' => [
                    'required',
                    Rule::unique('yz_admin_users')->where(function($q) use($user){
                        return $q->whereNull('deleted_at')->where('uid','<>',$user['uid']);
                    })
                ],
//                'mobile' => 'required|regex:/^1[3456789]\d{9}$/|unique:yz_users_profile,mobile,'.$user['hasOneProfile']['id'],
                'mobile' => [
                    'required',
                    'regex:/^1[3456789]\d{9}$/',
                    Rule::unique('yz_users_profile')->where(function($q) use($user){
                        return $q->whereNull('deleted_at')->where('id','<>',$user['hasOneProfile']['id']);
                    })
                ]
            ];
        }

        if (request()->path() != "admin/user/edit") {
            if (request()->path() == "admin/user/modify_user" && !$data['password']) {
                return $rules;
            }
            $rules['password'] = 'required';
            $rules['re_password'] = 'same:password';
        }
        return $rules;
    }

    /**
     * 表单验证自定义错误消息
     *
     * @return array
     */
    public function message()
    {
        return [
            'username.required' => '用户名不能为空',
            'username.regex' => '用户名格式不正确',
            'username.unique' => '用户名已存在',
            'mobile.required' => '手机号不能为空',
            'mobile.regex' => '手机号格式不正确',
            'mobile.unique' => '手机号已存在',
            'password.required' => '密码不能为空',
            're_password.same' => '两次密码不一致',
        ];
    }


    /**
     * 返回 json 信息
     *
     * @param $param
     * @return \Illuminate\Http\JsonResponse
     */
    public function check($param)
    {
        if ($param['sign'] == 1) {
            return $this->successJson('成功');
        } else {
            return $this->errorJson([$param['message']]);
        }
    }
}