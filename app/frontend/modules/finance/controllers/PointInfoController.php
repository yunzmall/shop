<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/12
 * Time: 下午4:00
 */

namespace app\frontend\modules\finance\controllers;

use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\models\finance\PointTransfer;
use app\common\models\Member;
use app\common\services\finance\PointService;
use app\frontend\modules\finance\models\PointLog;

class PointInfoController extends ApiController
{
    public function index()
    {
        $member_id = \YunShop::app()->getMemberId();
        $type = \YunShop::request()->status;
        $list = PointLog::getPointLogList($member_id, $type)->paginate(15);
        $set = Setting::get('point.set');
        if($set['show_transferor'] == 1)
        {
            $list = $this->handleList($list);
        }
        return $this->successJson('成功', [
            'list' => $list
        ]);
    }

    private function handleList($list)
    {
        $listModels = $list->toArray();
        $listModels['data'] = collect($listModels['data'])->map(function ($item) {
            $mark = false;
            if($item['point_mode'] == PointService::POINT_MODE_TRANSFER)//转出
            {
                if($item['relation_id'])
                {
                   $log =  PointTransfer::uniacid()->find($item['relation_id']);
                   if($log)
                   {
                       $member = Member::find($log->recipient);
                       $arr = [
                         'type' => '受让人',
                           'id' => $log->recipient,
                           'name' => $member->nickname
                       ];
                       $mark = true;
                   }
                }
            }elseif ($item['point_mode'] == PointService::POINT_MODE_RECIPIENT){
                $log =  PointTransfer::uniacid()->find($item['relation_id']);
                if($log)
                {
                    $member = Member::find($log->transferor);
                    $arr = [
                        'type' => '转让人',
                        'id' => $log->transferor,
                        'name' => $member->nickname
                    ];
                    $mark = true;
                }
            }
            if($mark)
            {
                return  collect($item)->put('transfer_info',$arr);
            }else{
                return  collect($item);
            }
        });
        return collect($listModels);
    }
}