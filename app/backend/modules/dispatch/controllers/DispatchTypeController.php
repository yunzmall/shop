<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/3
 * Time: 15:28
 */

namespace app\backend\modules\dispatch\controllers;


use app\backend\modules\dispatch\models\DispatchType;
use app\common\components\BaseController;
use app\common\models\DispatchTypeSet;
use Illuminate\Support\Facades\DB;

class DispatchTypeController extends BaseController
{
    public function index()
    {
        //$dispatchTypes = DispatchType::where('plugin', 0)->orderBy('sort', 'desc')->get();

        //$this->setDispatch();


        return view('dispatch.dispatch-type', [])->render();
    }

    //todo 非要加，实属无奈
    protected function setDispatch()
    {
        $set = \Setting::get('shop.trade');
        if (isset($set['is_dispatch']) && $set['is_dispatch']) {

            $bool = DispatchTypeSet::relationSave(DispatchType::where('id', 1)->first(), ['enable'=>0]);

            unset($set['is_dispatch']);

            \Setting::set('shop.trade', $set);
        }
    }

    public function getData()
    {
        $dispatchTypes = DispatchType::getCurrentUniacidSet();

        return $this->successJson('data',$dispatchTypes);
    }

    public function editEnable()
    {
        $id = intval(request()->input('id'));

        $enable = request()->input('enable');

        $dispatchType = DispatchType::where('id', $id)->first();

        if (is_null($dispatchType)) {
            return $this->errorJson('失败,配送方式不存在');
        }

        $bool = DispatchTypeSet::relationSave($dispatchType, ['enable'=> $enable]);

        if ($bool) {
            return $this->successJson('操作成功');
        } else {
            return $this->errorJson('操作失败');
        }
    }

    public function editSort()
    {
        $id = intval(request()->input('id'));

        $value = intval(request()->input('value'));


        $dispatchType = DispatchType::where('id', $id)->first();
        if (is_null($dispatchType)) {
            return $this->errorJson('排序失败,配送方式不存在');
        }

        $bool =  DispatchTypeSet::relationSave($dispatchType, ['sort'=>$value]);

        if ($bool) {
            return $this->successJson('排序成功');
        } else {
            return $this->errorJson('排序失败');
        }
    }

    public function bulkUpdateGoods()
    {
        $dispatch_type_ids = request()->input('dispatch_type_ids');


        if (empty($dispatch_type_ids)) {
            return $this->errorJson('请至少选择一种配送方式');
        }

        if (is_array($dispatch_type_ids)) {
            $dispatch_type_ids = implode(',', $dispatch_type_ids);

        }

        $rows =  DB::table('yz_goods_dispatch')->join('yz_goods','yz_goods.id','=','yz_goods_dispatch.goods_id')
            ->whereNull('yz_goods_dispatch.dispatch_type_ids')
            ->where('yz_goods.uniacid', \YunShop::app()->uniacid)
            ->update(['yz_goods_dispatch.dispatch_type_ids' => $dispatch_type_ids]);


        return $this->successJson('成功', $rows);
    }

}