<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/10
 * Time: 14:39
 */

namespace app\platform\modules\user\controllers;


use app\common\models\UniAccount;
use app\platform\modules\user\models\OfficialWebsiteContact;
use app\platform\modules\user\models\OfficialWebsiteMultiple;
use app\platform\modules\user\models\OfficialWebsiteTheme;
use Illuminate\Http\Request;
use app\platform\controllers\BaseController;
use Yunshop\Article\models\Article;
use Yunshop\Article\models\Category;
use Yunshop\HelpCenter\models\HelpCenterAddModel;

class ThemeSetController extends BaseController
{
    public function index()
    {
        $set = \Setting::get('official_website.theme_set');

        if (!isset($set['is_open'])) {
            $set['is_open'] = 1;
            $set['is_customize'] = 0;
            $set['customize_url'] = '';
            \Setting::set('official_website.theme_set',$set);
        }

        $data = request()->is_open;
        $is_customize = request()->is_customize;
        $customize_url = request()->customize_url;

        if ($data) {
            $set['is_open'] = $data;
            $set['is_customize'] = $is_customize;
            $set['customize_url'] = $customize_url;
            \Setting::set('official_website.theme_set',$set);
        }

        return $this->successJson('',$set);
    }

    public function createTheme()
    {
        $data = request()->data;

        if (empty($data)) {
            return $this->errorJson("数据为空");
        }

        $validate = $this->validate($this->rules(), $data, $this->message());

        if ($validate) {
            return $validate;
        }

        $official = new OfficialWebsiteTheme();
        $official->title = $data['title'];
        $official->is_default = $data['is_default'];

        if ($data['is_default'] == 1) {
            OfficialWebsiteTheme::where("is_default",1)->update(["is_default"=>0]);
        }

        if ($official->save()) {
            return $this->successJson('成功');
        } else {
            return $this->errorJson('失败');
        }
    }

    public function themeList()
    {
        $list = OfficialWebsiteTheme::orderBy('id','desc')->paginate(10);

        return $this->successJson("",$list);
    }

    public function editTheme()
    {
        $data = request()->data;
        $status = request()->status;
        $id = request()->id;

        if (empty($id) || empty($status)) {
            return $this->errorJson('参数不存在');
        }

        if (empty($data)) {
            $theme = OfficialWebsiteTheme::where("id",$id)->first();
            $theme = empty($theme) ? [] : $theme->toArray();
            //解压数据
            $theme_data = $this->processData($status,$theme);
            $theme_data['identification'] = $theme['identification'];
            $theme_data['uniAccount'] = UniAccount::select("uniacid","name")->get();
        } else {
            //保存
            $rel = $this->saveData($status,$data,$id);
            if ($rel == true) {
                return $this->successJson('成功');
            } else {
                return $this->errorJson('失败');
            }
        }

        return $this->successJson('',$theme_data);
    }

    public function processData($status,$data)
    {
        $result = [];
        if (empty($data)) {
            return $result;
        }

        if ($status == 1) {
            $result = !empty($data['basic']) ? json_decode($data['basic'],true) : [];
            if (isset($result['cus_url'])) {
                $result['cus_url'] = $result['cus_url'];
            }

        } elseif ($status == 2) {
            $result = !empty($data['top']) ? json_decode($data['top'],true) : [];
            if ($result['logo']) {
                $result['logo'] = $result['logo'];
            }
        } elseif ($status == 3) {
            if ($data['tail']) {
                $result['bottom'] = htmlspecialchars_decode($data['tail']);
            }
        }

        return $result;
    }

    public function saveData($status,$data,$id=0)
    {
        if (empty($id)) {
            $official = new OfficialWebsiteTheme();
        } else {
            $official = OfficialWebsiteTheme::where("id",$id)->first();
        }

        if ($status == 1) {
            $data = json_encode($data);
            $official->basic = $data;
        } elseif ($status == 2) {
            $data = json_encode($data);
            $official->top = $data;
        } elseif ($status == 3) {
            $data = htmlspecialchars($data['bottom']);
            $official->tail = $data;
        } elseif ($status == 4) {
            if ($data['is_default'] == 1) {
                OfficialWebsiteTheme::where("id",">",0)->update(["is_default"=>0]);
            }
            $official->is_default = $data['is_default'];
        }

        if ($official->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function dentList()
    {
        $theme = request()->theme_id;
        $theme = empty($theme) ? "" : $theme;
        $all_multiple = OfficialWebsiteMultiple::where("theme_id",$theme)->select("id","theme_id","name","identification")->get();
        $all_multiple = empty($all_multiple) ? [] : $all_multiple->toArray();

        return $this->successJson("",$all_multiple);
    }

    public function multipleList()
    {
        $data = request()->data;
        $theme_id = request()->theme_id;
        $theme_id = empty($theme_id) ? 0 : $theme_id;

        if (empty($theme_id) || !is_numeric($theme_id)) {
            return $this->errorJson("主题ID不能为空且必须为数字");
        }

        $theme_set = OfficialWebsiteTheme::where("id",$theme_id)->first();

        $identification = request()->identification;
        $identification = empty($identification) ? "" : $identification;

        if (!preg_match("/[a-zA-Z0-9_]{1,15}/",$identification)) {
            return $this->errorJson("路径名称只能是字母、数字、下划线且长度不超过15");
        }

        $multiple = [];
        if ($identification) {
            $multiple = OfficialWebsiteMultiple::where("theme_id",$theme_id)->where("identification",$identification)->first();
            $multiple = empty($multiple) ? [] : $multiple->toArray();
            if (isset($multiple['detail'])) {
                $multiple['detail'] = htmlspecialchars_decode($multiple['detail']);
            }
            $all_multiple = OfficialWebsiteMultiple::where("theme_id",$theme_id)->select("id","theme_id","name","identification")->get();
            $all_multiple = empty($all_multiple) ? [] : $all_multiple->toArray();
            $multiple['all_multiple'] = $all_multiple;
        }

        if ($data) {

            $repeat = OfficialWebsiteMultiple::where("identification",$identification)->where("theme_id",$theme_id)->first();

            if ($repeat) {
                $official = $repeat;
            } else {
                $official = new OfficialWebsiteMultiple();
            }

            $data['url'] = "https://".$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=".$identification;

            $validate = $this->validate($this->rules(), $data, $this->message());

            if ($validate) {
                return $validate;
            }
            $article = "";
            $help = "";
            if ($identification == "resource_page") {

                $basic = empty($theme_set) ? [] : json_decode($theme_set['basic'],true);

                $cus_uniacid = empty($basic['cus_uniacid']) ? 0 : $basic['cus_uniacid'];

                if (app('plugins')->isEnabled('article')) {

                    $article = Category::where("uniacid",$cus_uniacid)->with(["hasManyArticle"=>function ($query){
                        $query->select("id","category_id","title","desc","thumb","author","created_at","content")->where('state', 1)->where('type', '!=', 1)->orderBy("id","DESC")->offset(0)->limit(15);
                    }])->get();

                    $article = empty($article) ? "" : json_encode($article->toArray());
                }

                if (app('plugins')->isEnabled('help-center')) {
                    $help = HelpCenterAddModel::where("uniacid",$cus_uniacid)->select('title', 'content')->orderBy('sort')->offset(0)->limit(25)->get();
                    $help = empty($help) ? "" : json_encode($help->toArray());
                }
            }

            $official->theme_id = $theme_id;
            $official->name = $data['name'];
            $official->identification = $identification;
            $official->mul_title = $data['mul_title'];
            $official->url = $data['url'];
            $official->keyword = $data['keyword'];
            $official->describe = $data['describe'];
            $official->detail = htmlspecialchars($data['detail']);
            $official->article = $article;
            $official->helper = $help;
            if ($official->save()) {

                if ($theme_set['identification'] == "common") {
                    if (!file_exists(base_path("/official/".$theme_set['identification']."/" . $identification . ".html"))) {

                        $room = '<!DOCTYPE html>
<html>
  <head lang="en">
    <meta http-equiv="content-Type" content="text/html;charset=UTF-8" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*" />
    <meta name="keywords" content="<?php echo $keywords ?>">
    <meta name="description" content="<?php echo $describe ?>">
    <title><?php echo $title ?></title>
    <link rel="stylesheet" href="official/css/main.css" />
  </head>

  <body>
    <div id="app">
      <div class="nav">
        <div class="nav-a">
          <div class="nav-logo">
            <img src="<?php echo $top["logo"]?>" alt="" />
          </div>
          <?php foreach($top["navigation"] as $kk=>$vv) {?>
          <div class="nav-li <?php if($vv["tool"]==$name){?>nav-selected<?php }?>">
            <a href="<?php echo $vv["url"]?>"><?php echo $vv["name"]?></a>
          </div>
          <?php }?>
          <?php if ($top["is_sign"] == 1) {?>
          <a href="#" id="jump-url">
          <div class="nav-login">立即登录</div>
          </a>
          <?php }?>
        </div>
      </div>

      <?php echo $content;?>

      <?php echo $tail;?>

      <div class="all-relation">
        <div class="relation-btn" id="relation-btn">
          <div class="relation-btn-one" id="callus">
            <img src="official/images/lianxikefu.png" alt="">
            <div>联系客服</div>
          </div>
          <div class="relation-btn-one" id="back-top">
            <img src="official/images/huidaodingbu.png" alt="">
            <div>回到顶部</div>
          </div>
        </div>
        <div class="relation">
          <div class="relation-title">在线咨询</div>
          <a href="<?php echo $basic[\'cus_link\']?>" title="点击咨询">
            <div class="relation-tel">
              <div class="relation-tel-img">
                <img src="official/images/suspension_qq.png" alt="" />
              </div>
              <div class="relation-tel-word">在线客服</div>
            </div>
          </a>
          <div class="relation-tel">
            <div class="relation-tel-img">
              <img src="official/images/suspension_tel.png" alt="" />
            </div>
            <div class="relation-tel-word"><?php echo $basic["cus_mobile"]?></div>
          </div>
          <div class="relation-tel relation-tel1">
            <div class="relation-tel-img">
              <img src="official/images/suspension_wechat.png" alt="" />
            </div>
            <div class="relation-tel-word">微信二维码</div>
          </div>
          <div class="qr-img">
            <div class="qr-img"><img src="<?php echo $basic["cus_url"]?>" alt="" /></div>
          </div>
          <img id="close-btn" style="position: absolute;top:5px;right:5px" src="official/images/adsystem_icon_cancle.png" alt="">
        </div>
      </div>
    </div>
          <script type="text/javascript">
              var url = "";
                window.onload = function () {
                                
                    url = window.location.protocol + "//" + window.location.host + "/admin.html";
                    if(document.getElementById("jump-url")) {
                        document.getElementById("jump-url").setAttribute("href", url);
                    }

                    document.getElementById("callus").onclick = function(e) {
                        document.getElementById("relation-btn").style.display="none";
                        document.getElementsByClassName("all-relation")[0].classList.add("all-relation-hover")
                    }
                    document.getElementById("close-btn").onclick = function(e) {
                        document.getElementById("relation-btn").style.display="inline-block";
                        document.getElementsByClassName("all-relation")[0].classList.remove("all-relation-hover")
                    }
        
                    var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                    if(scrollTop>0) {
                        document.getElementById("back-top").style.display = "block"
                    }
                    else{
                        document.getElementById("back-top").style.display = "none"
                    }
        
                    document.getElementById("back-top").onclick = function(e) {
                        if(document.documentElement.scrollTop) {
                            document.documentElement.scrollTop = 0;
                        }
                        else{
                            document.body.scrollTop = 0;
                        }
                    }
        
                    window.onscroll = function() {
                        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                        if(scrollTop>0) {
                            document.getElementById("back-top").style.display = "block"
                        }
                        else{
                            document.getElementById("back-top").style.display = "none"
                        }
                    }
                };
            </script>
  </body>
</html>';
                        file_put_contents(base_path("/official/".$theme_set['identification']."/" . $identification . ".html"), $room);
                    }
                } elseif ($theme_set['identification'] == "website") {
                    if (!file_exists(base_path("/official/".$theme_set['identification']."/" . $identification . ".html"))) {

                        $room = '<!DOCTYPE html>
<html>
  <head lang="en">
    <meta http-equiv="content-Type" content="text/html;charset=UTF-8" />
    <meta http-equiv="Access-Control-Allow-Origin" content="*" />
    <meta name="keywords" content="<?php echo $keywords ?>">
    <meta name="description" content="<?php echo $describe ?>">
    <title><?php echo $title ?></title>
    <link rel="stylesheet" href="official/website/css/main.css" />
  </head>

  <body>
    <div id="app">
      <div class="nav">
        <div class="nav-a">
          <div class="nav-logo">
            <img src="<?php echo $top["logo"]?>" alt="" />
          </div>
          <?php foreach($top["navigation"] as $kk=>$vv) {?>
          <div class="nav-li <?php if($vv["tool"]==$name){?>nav-selected<?php }?>">
            <a href="<?php echo $vv["url"]?>"><?php echo $vv["name"]?></a>
          </div>
          <?php }?>
          <?php if ($top["is_sign"] == 1) {?>
          <a href="#" id="jump-url">
          <div class="nav-login">立即登录</div>
          </a>
          <?php }?>
        </div>
      </div>

      <?php echo $content;?>

      <?php echo $tail;?>

      <div class="all-relation">
        <div class="relation-btn" id="relation-btn">
          <div class="relation-btn-one" id="callus">
            <img src="official/website/images/lianxikefu.png" alt="" />
            <div>联系客服</div>
          </div>
          <div class="relation-btn-one" id="back-top">
            <img src="official/website/images/huidaodingbu.png" alt="" />
            <div>回到顶部</div>
          </div>
        </div>
        <div class="relation">
          <div class="relation-title">在线咨询</div>
          <a href="<?php echo $basic[\'cus_link\']?>" title="点击咨询">
            <div class="relation-tel">
              <div class="relation-tel-img">
                <img src="official/website/images/suspension_qq.png" alt="" />
              </div>
              <div class="relation-tel-word">在线客服</div>
            </div>
          </a>
          <div class="relation-tel">
            <div class="relation-tel-img">
              <img src="official/website/images/suspension_tel.png" alt="" />
            </div>
            <div class="relation-tel-word"><?php echo $basic[\'cus_mobile\']?></div>
          </div>
          <div class="relation-tel relation-tel1">
            <div class="relation-tel-img">
              <img src="official/website/images/suspension_wechat.png" alt="" />
            </div>
            <div class="relation-tel-word">微信二维码</div>
          </div>
          <div class="qr-img">
            <img src="<?php echo $basic[\'cus_url\']?>" alt="" />
          </div>
          <img id="close-btn" style="position: absolute; top: -15px; right: -25px" src="official/website/images/adsystem_icon_cancle.png" alt=""/>
        </div>
      </div>
    </div>
          <script type="text/javascript">
              var url = "";
                window.onload = function () {
                                
                    url = window.location.protocol + "//" + window.location.host + "/admin.html";
                    if(document.getElementById("jump-url")) {
                        document.getElementById("jump-url").setAttribute("href", url);
                    }

                    document.getElementById("callus").onclick = function(e) {
                        document.getElementById("relation-btn").style.display="none";
                        document.getElementsByClassName("all-relation")[0].classList.add("all-relation-hover")
                    }
                    document.getElementById("close-btn").onclick = function(e) {
                        document.getElementById("relation-btn").style.display="inline-block";
                        document.getElementsByClassName("all-relation")[0].classList.remove("all-relation-hover")
                    }
        
                    var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                    if(scrollTop>0) {
                        document.getElementById("back-top").style.display = "block"
                    }
                    else{
                        document.getElementById("back-top").style.display = "none"
                    }
                    
                    document.getElementById("back-top").onclick = function(e) {
                        if(document.documentElement.scrollTop) {
                            document.documentElement.scrollTop = 0;
                        }
                        else{
                            document.body.scrollTop = 0;
                        }
                    }
        
                    window.onscroll = function() {
                        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                        if(scrollTop>0) {
                            document.getElementById("back-top").style.display = "block"
                        }
                        else{
                            document.getElementById("back-top").style.display = "none"
                        }
                    }
                };
            </script>
  </body>
</html>';
                        file_put_contents(base_path("/official/".$theme_set['identification']."/" . $identification . ".html"), $room);
                    }
                }

                return $this->successJson("成功");
            } else {
                return $this->errorJson("失败");
            }
        }

        return $this->successJson("",$multiple);
    }

    public function contactList()
    {
        $list = OfficialWebsiteContact::orderBy("id","desc")->paginate(10);

        return $this->successJson("",$list);
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
     * 表单验证自定义错误消息
     *
     * @return array
     */
    public function message()
    {
        return [
            'title.required' => '主题名称不能为空',
            'name.required' => '页面名称不能为空',
            'mul_title.required' => '页面标题不能为空',
            'keyword.required' => '关键字不能为空',
            'url.required' => '路径不能为空'
        ];
    }

    /**
     * 表单验证规则
     *
     * @param $user
     * @param $data
     * @return array
     */
    public function rules()
    {
        $rules = [];
        if (request()->path() == "admin/user/create_theme") {
            $rules = [
                'title' => 'required',
            ];
        } elseif (request()->path() == "admin/user/multiple_list") {
            $rules = [
                'name' => 'required',
                'mul_title' => 'required',
                'keyword' => 'required',
                'url' => 'required'
            ];
        }

        return $rules;
    }

}