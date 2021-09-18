<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/9
 * Time: 17:06
 */

namespace app\common\services\notice\share;


use app\common\models\McMappingFans;

Trait BackstageNoticeMember
{
    public $member_ids=[];//需要收消息的会员组
    public $openids=[];//会员组的openid
    public $minMemberIds=[];//小程序端
    public $minOpenIds=[]; //小程序端

    public function getBackMember()
    {
        $salers = \Setting::get('shop.notice.salers');

        if (!empty($salers)) {
            foreach ($salers as $kk=>$vv) {
                $this->member_ids[] = $vv['uid'];
//                $this->openids[] = $vv['openid'];
            }
            if($this->member_ids){
                $fans = McMappingFans::uniacid()->select('openid')->whereIn('uid',$this->member_ids)->get();
                if(!$fans->isEmpty()){
                    $fans = $fans->toArray();
                    $this->openids = array_column($fans,'openid');
                }
            }
        }

        $min_salers = \Setting::get('mini_app.notice.salers');

        if (!empty($min_salers)) {
            foreach ($min_salers as $kk=>$vv) {
                $this->minMemberIds[] = $vv['uid'];
                $this->minOpenIds[] = $vv['openid'];
            }
        }
    }
}