<?php
/**
 * Created by PhpStorm.
 * Author:
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
        $pointModel = new PointLog();
        $member_id = \YunShop::app()->getMemberId();
        $type = \YunShop::request()->status;
        $mode = \YunShop::request()->mode;
        $month = \YunShop::request()->month;
        $set = Setting::get('point.set');
        if (!versionCompare('1.1.138') || !miniVersionCompare('1.1.138')) {
            $result = $pointModel->getPointLogList($member_id, $type);
            if ($set['show_transferor'] == 1) {
                $result['record_list'] = $this->oldHandleList($result);
            }
            $result=['list'=>$result];
        }else{
            $result = $pointModel->getPointRecord($member_id, $type, $mode, $month);
            if ($set['show_transferor'] == 1) {
                $result['record_list'] = $this->handleList($result['record_list']);
            }
        }
        return $this->successJson('成功',$result);
    }

    public function detail()
    {
        $log_id = \YunShop::request()->log_id;
        $data = PointLog::select('id','member_id','point','point_mode','before_point','after_point','remark','created_at')
            ->find($log_id);
		$point_name = \Setting::get('shop.shop.credit1') ?: '积分';
		$data['remark'] = str_replace('积分',$point_name,$data['remark']);
        $data['remark'] = str_replace($point_name . '通', '积分通', $data['remark']);
        return $this->successJson('成功',$data);
    }

    private function oldHandleList($list)
    {

        $listModels = $list;
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


    private function handleList($list)
    {

        $listModels = $list;
        $listModels['data'] = collect($listModels['data'])->map(function ($item) {
           $item=collect($item)->map(function($items){
               $mark = false;
               if($items['point_mode'] == PointService::POINT_MODE_TRANSFER)//转出
               {
                   if($items['relation_id'])
                   {
                       $log =  PointTransfer::uniacid()->find($items['relation_id']);
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
               }elseif ($items['point_mode'] == PointService::POINT_MODE_RECIPIENT){
                   $log =  PointTransfer::uniacid()->find($items['relation_id']);
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

                   return  collect($items)->put('transfer_info',$arr);
               }else{
                   return  collect($items);
               }
           });
            return  collect($item);
        });
        return collect($listModels);
    }
}
