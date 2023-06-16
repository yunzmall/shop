<?php
/**
 * Created by PhpStorm.
 * User: king/QQ:995265288
 * Date: 2019-06-19
 * Time: 16:52
 */

namespace app\backend\modules\excelRecharge\controllers;


use app\backend\models\excelRecharge\DetailModel;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;

class DetailController extends BaseController
{
    /**
     * @var DetailModel
     */
    protected $recordsModels;


    //会员excel充值详细记录
    public function index()
    {
        if (request()->ajax()) {
            $this->recordsModels = $this->pageList();
            $shopSet = Setting::get('shop.member');
            $this->recordsModels->map(function ($item) use ($shopSet) {
                $item->member->avatar = $item->member->avatar ? tomedia($item->member->avatar) : tomedia($shopSet['headimg']);
                $item->member->nickname = $item->member->nickname ?: '未更新';
                $item->member->uid = $item->member->uid ?: '';
            });
            return $this->successJson('ok', $this->resultData());
        }

        return view('excelRecharge.detail');
    }

    private function resultData()
    {
        return [
            'pageList' => $this->recordsModels
        ];
    }

    /**
     * @return DetailModel
     */
    private function pageList()
    {
        $records = DetailModel::uniacid()->with('member');

        $rechargeId = $this->rechargeIdParam();
        if ($rechargeId) {
            $records->where('recharge_id', $rechargeId);
        }
        return $records->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate('', ['*'], '', $this->pageParam());
    }

    /**
     * @return int
     */
    private function pageParam()
    {
        return (int)request()->page ?: 1;
    }

    /**
     * @return int
     */
    private function rechargeIdParam()
    {
        return (int)request()->recharge_id ?: 1;
    }
}
