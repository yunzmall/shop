<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/27
 * Time: 下午4:26
 */

namespace app\frontend\controllers;

use app\common\components\BaseController;
use app\common\services\SystemMsgService;
use app\common\models\UniAccount;
use app\common\facades\Setting;

class SendMsgController extends BaseController
{
    //校验
    public function checkSign()
    {
        $sign = request()->sign;
        $timeStamp = request()->timeStamp;
        if(empty($sign) || empty($timeStamp)){
            return [
                'result' => 0,
                'msg' => '参数错误'
            ];
        }
        if((time()-$timeStamp) > 60){
            return [
                'result' => 0,
                'msg' => '请求已过时'
            ];
        }
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            //循环取出key，一一对比，一个对了则通过
            Setting::$uniqueAccountId = $u->uniacid;
            $upgrade = Setting::get('shop.key');
            if(!empty($upgrade) && $sign == md5($upgrade['key'].$timeStamp)){
                return  [
                    'result' => 1
                ];
            }
        }
        return [
            'result' => 0,
            'msg' => '签名错误'
        ];
    }

    //推送紧急系统通知
    public function pushMessage()
    {
        $checkRes = $this->checkSign();
        if($checkRes['result'] == 0){
            return $this->errorJson($checkRes['msg']);
        }

        $request = request()->all();
        if(empty($request['content']))
        {
            return $this->errorJson('通知内容必须填写');
        }
        $param = [
            'title' => $request['title']?$request['title']:'您有一条紧急通知，请及时查看！',
            'content' => $request['content'],
            'redirect_url' => ''
        ];

        $uniAccount = UniAccount::getEnable();
        $success = 0;
        $fail = 0;
        foreach ($uniAccount as $u) {
            $res = (new SystemMsgService($u->uniacid))->sendSysMsg(1,$param);
            if($res){
                $success++;
            }else{
                $fail++;
            }
        }
        if($success > 0){
            return $this->successJson('成功:'.$success.',失败:'.$fail);
        }
        return $this->errorJson('推送紧急通知失败');
    }

    //插件升级
    public function pluginUpgrade()
    {
        $checkRes = $this->checkSign();
        if($checkRes['result'] == 0){
            return $this->errorJson($checkRes['msg']);
        }

        $param = [
            'title' => '您有新的系统更新信息，请注意查看!',
            'content' => '时间:'.date('Y-m-d H:i:s'),
            'redirect_url' => yzWebUrl('plugin.plugins-market.Controllers.new-market.show')
        ];

        $uniAccount = UniAccount::getEnable();
        $success = 0;
        $fail = 0;
        foreach ($uniAccount as $u) {
            $res = (new SystemMsgService($u->uniacid))->sendSysMsg(1,$param);
            if($res){
                $success++;
            }else{
                $fail++;
            }
        }
        if($success > 0){
            return $this->successJson('成功:'.$success.',失败:'.$fail);
        }
        return $this->errorJson('推送插件升级通知失败');
    }

}