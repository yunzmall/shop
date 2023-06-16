<?php

/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/7/1
 * Time: 14:55
 */

namespace app\backend\modules\sysMsg\controllers;

use app\common\components\BaseController;
use app\common\models\systemMsg\SysMsgLog;
use app\common\services\SystemMsgService;
use Illuminate\Support\Facades\DB;

class SystemMsgController extends BaseController
{
    private $logCount = [];

    public function __construct()
    {
        $data = SysMsgLog::uniacid()
            ->select(DB::raw('type_id,COUNT(id) as log_count'))
            ->where('is_read', 0)
            ->groupBy('type_id')
            ->get()->toArray();
        $data = array_column($data,null,'type_id');
        $this->logCount = SystemMsgService::$msg_type;
        $total = 0;
        foreach ($this->logCount as $k => $item) {
            $this->logCount[$k]['has_many_log_count'] = $data[$item['id']]['log_count'] ? : 0;
            $total += $this->logCount[$k]['has_many_log_count'];
        }
        $this->logCount[] = [
            'id' => 0,
            'type_name' => '全部消息',
            'icon_src' => '',
            'has_many_log_count' => $total
        ];
        $this->logCount = array_column($this->logCount, null, 'id');
        asort($this->logCount);
    }

    //进去的首页
    public function index()
    {
        //订单收货测试
        $search = request()->search;
        $pageSize = 10;
        $list = SysMsgLog::getLogList(0, $search)
//            ->with('belongsToType')
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize)
            ->toArray();
        return view('sysMsg.index',[
            'list' => $list,
            'search' => $search,
            'msgType' => $this->logCount
        ])->render();
    }

    public function allMessage()
    {
        return $this->getList(0);
    }

    public function sysMessage()
    {
        return $this->getList(1);
    }

    public function orderMessage()
    {
        return $this->getList(2);
    }

    public function withdrawalMessage()
    {
        return $this->getList(3);
    }

    public function applyMessage()
    {
        return $this->getList(4);
    }

    public function stockMessage()
    {
        return $this->getList(5);
    }

    public function couponMessage()
    {
        return $this->getList(6);
    }

    public function refundMessage()
    {
        return $this->getList(7);
    }

    public function getList($type = 0)
    {
        $search = request()->search;
        $pageSize = 10;
        $list = SysMsgLog::getLogList($type, $search)
//            ->with('belongsToType')
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize)
            ->toArray();
        return $this->successJson('ok',[
            'list' => $list,
            'search' => $search,
            'msgType' => $this->logCount
        ]);
    }

    //更改消息已读状态
    public function readLog()
    {
        $type = request()->type;
        if (!empty($type) && $type == 1) {
            //全部标记已读
            SysMsgLog::uniacid()
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
            return $this->successJson('ok');
        }
        $id = request()->id;
        if(empty($id)){
            return $this->errorJson('参数错误');
        }
        $log = SysMsgLog::uniacid()->find($id);
        if (empty($log)) {
            return $this->errorJson('未找到消息或已删除');
        }
        if ($log->is_read == 0) {
            $log->is_read = 1;
            $log->read_at = time();
            $log->save();
        }
        return $this->successJson('ok');
    }

    //查看系统通知类型消息详情
    public function readSystemMessage()
    {
        $id = \YunShop::request()->id;
        if(empty($id)){
            return $this->message('参数错误','','error');
        }
        $log = SysMsgLog::uniacid()->find($id);
        if (empty($log)) {
            return $this->message('未找到消息或已删除','','error');
        }
        if ($log->is_read == 0) {
            $log->is_read = 1;
            $log->read_at = time();
            $log->save();
        }
        return view('sysMsg.detail',[
            'data' => $log,
        ])->render();
    }

}
