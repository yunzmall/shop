<?php
/**
 * Author:
 * Date: 2017/11/8
 * Time: 下午4:20
 */

namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\MiniTemplateCorresponding;
use app\common\models\notice\MessageTemp;
use app\backend\modules\setting\controllers\SmallProgramDataController;
use app\backend\modules\setting\controllers\DiyTempController;
use app\common\models\notice\MinAppTemplateMessage;
use app\common\services\notice\SmallProgramNotice;
use app\common\models\MemberMiniAppModel;

class SmallProgramController extends BaseController
{
    private $temp_model;
    protected $SmallProgramNotice;
    public function __construct(){
        $this->SmallProgramNotice = new SmallProgramNotice();
    }

    public function index()
    {
        $list = $this->SmallProgramNotice->getExistTemplateList();
        $list = empty($list) ? [] : json_decode($list,'true');
        if ($list['errcode'] != 0 || !isset($list['errcode'])){
            return $this->message('获取模板失败'.$list,'', 'error');
        }
        return view('setting.small-program.detail1', [
            'list'=>$list['data'],
        ])->render();
    }
    public function synchronization(){
        $title_list = [
            '1536' => ['keyword'=>['6','1','3','4','5'],'scene'=>'新订单提醒'],
            '1648' => ['keyword'=>['1','4','6','3','8'],'scene'=>'订单支付成功通知'],
            '1537' => ['keyword'=>['5','3','4','1','2'],'scene'=>'佣金到账提醒'],
            '9086' => ['keyword'=>['3','1','5','2','4'],'scene'=>'粉丝下单通知'],
            '5778' => ['keyword'=>['1','2'],'scene'=>'审核结果通知'],
            '9746' => ['keyword'=>['1','3','2','4'],'scene'=>'提现状态通知'],
            '3885' => ['keyword'=>['8','4','6','3','7'],'scene'=>'确认收货通知'],
            '1856' => ['keyword'=>['6','5','9','7','3'],'scene'=>'订单发货通知'],
            '9353' => ['keyword'=>['1','2','3','4','5'],'scene'=>'订单退款通知'],
            '3874' => ['keyword'=>['1','2','3'],'scene'=>'提现成功通知']
            ];

        $time = time();

        $corres_list = [
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>1,"template_name"=>"订单支付成功通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>1,"template_name"=>"订单发货通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>1,"template_name"=>"确认收货通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>2,"template_name"=>"订单退款通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>3,"template_name"=>"提现状态通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>3,"template_name"=>"提现成功通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>4,"template_name"=>"新订单提醒","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>4,"template_name"=>"提现状态通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>4,"template_name"=>"提现成功通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>5,"template_name"=>"新订单提醒","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>6,"template_name"=>"审核结果通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>6,"template_name"=>"粉丝下单通知","created_at"=>$time,"updated_at"=>$time],
            ["uniacid"=>\YunShop::app()->uniacid,"small_type"=>6,"template_name"=>"佣金到账提醒","created_at"=>$time,"updated_at"=>$time]
        ];

        //先将corres添加到数据库中
        MiniTemplateCorresponding::uniacid()->delete();
        MiniTemplateCorresponding::insert($corres_list);

        //获取列表
        $tempList = $this->SmallProgramNotice->getExistTemplateList();

        $temp_name = [];
        if ($tempList) {
            $tempList = json_decode($tempList, true);
            foreach ($tempList['data'] as $kk => $vv) {
                $temp_name[] = $vv['title'];
            }
        }

        $hasTempId = [];//记录该账号拥有的template_id,用于删除表中原先有的模板数据但同步后不存在的

        foreach ($title_list as $kk=>$vv) {
            $template_id = "";
            if (!in_array($vv['scene'], $temp_name)) {
                $result = $this->SmallProgramNotice->getAddTemplate($kk, $vv['keyword'], $vv['scene']);

                if (!$result['priTmplId']) {
                    \Log::debug($vv['scene'] . "添加失败");
                    continue;
                }

                $template_id = $result['priTmplId'];
            } else {
                foreach ($tempList['data'] as $k => $v) {
                    if ($v['title'] == $vv['scene']) {
                        $template_id = $v['priTmplId'];
                    }
                }
                if(empty($template_id)){
                    continue;
                }
            }

            $mini_app_temp = MinAppTemplateMessage::uniacid()->where("template_id", $template_id)->where("title", $vv['scene'])->first();

            if (!$mini_app_temp) {
                MinAppTemplateMessage::create([
                    'uniacid' => \YunShop::app()->uniacid,
                    'title' => $vv['scene'],
                    'template_id' => $template_id,
                    'is_default' => 1,
                    'small_type' => 1,
                    'data' => $vv['content'],
                ]);
            }

            $hasTempId[] = $template_id;

            MiniTemplateCorresponding::uniacid()->where("template_name",$vv['scene'])->update(["template_id"=>$template_id]);
        }

        MinAppTemplateMessage::uniacid()->whereNotIn("template_id",$hasTempId)->delete();

        return $this->successJson("同步完成",Url::absoluteWeb('setting.small-program.index'));

    }

    public function setNotice(){
        $tempId = request()->id;
        $tempOpen = request()->open;
        $is = MinAppTemplateMessage::isOpen($tempId,$tempOpen);
        $is_open = $is ? 1 : 0;
        echo json_encode([
            'result' => $is_open,
        ]);
    }
//    public function addTmp()
//    {
//        if (!request()->templateidshort) {
//            return $this->errorJson('请填写模板编码');
//        }
//        $ret = $this->WechatApiModel->getTmpByTemplateIdShort(request()->templateidshort);
//        if ($ret['status'] == 0) {
//            return $this->errorJson($ret['msg']);
//        } else {
//            return $this->successJson($ret['msg'], []);
//        }
//    }
//
//    public function getTemplateKey(){
//        if (isset(request()->key_val)){
//            $ret = $this->save(request()->all());
//            if (!$ret){
//                return $this->message('添加模板失败', Url::absoluteWeb('setting.small-program.index'), 'error');
//            }
//            return $this->message('添加模板成功', Url::absoluteWeb('setting.small-program.index'));
//        }
//        $page = request()->page;
//        if (isset(request()->id)){
//            $key_list = $this->SmallProgramNotice->getTemplateKey(request()->id);
//            if ($key_list['errcode'] != 0 || !isset($key_list['errcode'])){
//                return $this->message('获取模板失败', Url::absoluteWeb('setting.small-program.index'), 'error');
//            }
//            $keyWord = $key_list['keyword_list'];
//        }
//        return view('setting.small-program.detail', [
//            'keyWord'=>$keyWord,
//            'list'=>$this->SmallProgramNotice->getAllTemplateList($page)['list'],
//            'page'=>$page,
//            'title'=>request()->title,
//            'url'=>'setting.small-program.save'
//        ])->render();
//   }
//   public function save($list){
//       $strip = 0;
//        $date['data'] = [];
//        foreach ($list['key_val'] as $value){
//            $key_list[] = explode(":",$value)[0];
//        }
//        $template_date = $this->SmallProgramNotice->getAddTemplate($list['id'],$key_list);
//       if ($template_date['errcode'] != 0 || !isset($template_date['errcode'])){
//           return $this->message('添加模板失败', Url::absoluteWeb('setting.small-program.index'), 'error');
//       }
//        if ($template_date['errcode'] == 0){
//            $ret = MinAppTemplateMessage::create([
//                'uniacid' => \YunShop::app()->uniacid,
//                'title' =>  $list['title'],
//                'template_id' => $template_date['template_id'],
//                'keyword_id'=>implode(",", $list['key_val']),
//                'title_id'=>$list['id'],
//                'offset'=>$list['offset']
//            ]);
//           return $ret;
//        }
//   }

//    public function edit()
//    {
//        if (request()->id) {
//            $min_small = new MinAppTemplateMessage;
//            $temp_date = $min_small::getTemp(request()->id);//获取数据表中的数据
//            $key_list = $this->SmallProgramNotice->getTemplateKey($temp_date->title_id);
//        }
//        if (request()->key_val) {
//            foreach (request()->key_val as $value){
//                $keyWord_list[] = explode(":",$value)[0];
//            }
//            $template_date = $this->SmallProgramNotice->getAddTemplate($temp_date->title_id, $keyWord_list);
//            if ($template_date['errcode'] != 0 || !isset($template_date['errcode'])){
//                return $this->message('修改模板失败', Url::absoluteWeb('setting.small-program.index'), 'error');
//            }
//            $del_temp = $this->SmallProgramNotice->deleteTemplate($temp_date->template_id);//删除原来的模板
//            $temp_date->keyword_id = implode(",", request()->key_val);
//            $temp_date->template_id =$template_date['template_id'];
//            $ret = $temp_date->save();
//            if (!$ret) {
//                return $this->message('修改模板失败', Url::absoluteWeb('setting.small-program.index'), 'error');
//            }
//            return $this->message('修改模板成功', Url::absoluteWeb('setting.small-program.index'));
//        }
//
//        if ($key_list['errcode']==0){
//            $keyWord = $key_list['keyword_list'];
//        }
//        return view('setting.small-program.detail', [
//            'keyWord'=>$keyWord,
//            'is_edit'=>0,
//            'title'=>$temp_date->title,
//            'id'=>$temp_date->title_id,
//            'list'=>$this->SmallProgramNotice->getAllTemplateList($temp_date->offset)['list'],
//            'page'=>$temp_date->offset,
//            'url'=>'setting.small-program.save'
//        ])->render();
//        }


    public function notice()
    {
        $notice = \Setting::get('mini_app.notice');
        $requestModel = request()->yz_notice;
        $temp_list = MinAppTemplateMessage::getTempList();
        if (!empty($requestModel)) {
            if (\Setting::set('mini_app.notice', $requestModel)) {
                return $this->successJson(' 消息提醒设置成功', Url::absoluteWeb('setting.small-program.notice'));
            } else {
                $this->errorJson('消息提醒设置失败');
            }
        }
        if(request()->ajax()){
            return $this->successJson('请求接口成功',[
                'set' => $notice,
                'temp_list' => $temp_list
            ]);
        }
        return view('setting.small-program.notice');
    }


    public function see()
    {
        $list = $this->SmallProgramNotice->getExistTemplateList();

        $list = empty($list) ? [] : json_decode($list,'true');

        $tmp = '';
        foreach ($list['data'] as $temp) {
            while ($temp['priTmplId'] == request()->tmp_id)
            {
                $tmp = $temp;
                break;
            }
        }

        return view('setting.wechat-notice.small-see', [
            'template' => $tmp,
        ])->render();
    }



//     public function del()
//     {
//            if (request()->template_id) {
//                $min_small = new MinAppTemplateMessage;
//                $temp = $min_small::getTemp(request()->template_id);
//                if (empty($temp)) {
//                    return $this->message('找不到该模板', Url::absoluteWeb('setting.small-program.index'), 'error');
//                }
//                $ret = $list = $this->SmallProgramNotice->deleteTemplate(request()->template_id);
//                if ($ret['errcode'] == 0) {
//                    $min_small->delTempDataByTempId($temp->id);
//                   $kwd = request()->keyword;
//                    $list = MinAppTemplateMessage::getList();
//                    return view('setting.small-program.list', [
//                        'list' => $list->toArray(),
//                        'kwd' => $kwd,
//                        'url' => 'setting.small-program.save'
//                    ])->render();
//                }
//            }
//     }

    }