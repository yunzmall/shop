<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/29
 * Time: 9:51
 */

namespace app\backend\modules\setting\controllers;

use app\common\components\BaseController;
use app\common\helpers\Url;
use app\common\models\OperationLog;
use app\common\helpers\PaginationHelper;

class OperationLogController extends BaseController
{
    public function index()
    {

        if(request()->ajax()){
            $requestSearch = request()->search;
            if ($requestSearch) {
                $requestSearch = array_filter($requestSearch, function ($item) {
                    return $item !== '';// && $item !== 0;
                });

            }

            $list = OperationLog::Search($requestSearch)->orderBy('id', 'desc')->paginate(20);


            return $this->successJson('请求接口成功',[
                'list' => $list,
                'search' => $requestSearch,
            ]);
        }

        return view('setting.operation.log');
    }

    public function del()
    {
        $start =  request()->start;
        $end = request()->end;
        if (empty($start) || empty($end)) {
            return json_encode(['result' => 0, 'msg'=>'时间不能为空']);
        }

        $del = OperationLog::del($start, $end)->delete();

        if ($del) {
            return $this->successJson('删除成功');
        }
        return json_encode(['result' => 0, 'msg'=>'删除失败']);

    }
}