<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/2/20
 * Time: 上午11:09
 */

namespace app\platform\controllers;

use app\common\exceptions\AdminException;
use app\platform\modules\user\models\AdminUser;
use app\platform\modules\user\models\OfficialWebsiteContact;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use app\platform\modules\user\models\YzUserProfile;
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

        //获取最新的文章
        $start = $page*3;
        $news = Article::uniacid()->where("state",1)->where("type","!=",1)->orderBy("id","desc")->offset($start)->limit(3)->get();
        $news = empty($news) ? [] : $news->toArray();

        if ($news) {
            foreach ($news as $kk=>$vv) {
                $news[$kk]['detail_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$vv['id'];
            }
        }

        //获取虚拟阅读量多的文章
        $read = Article::uniacid()->where("state",1)->where("type","!=",1)->orderBy("virtual_read_num","desc")->offset($start)->limit(3)->get();
        $read = empty($read) ? [] : $read->toArray();

        if ($read) {
            foreach ($read as $kk=>$vv) {
                $read[$kk]['detail_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$vv['id'];
            }
        }

        return $this->successJson("",["news"=>$news,"read"=>$read]);

    }
}