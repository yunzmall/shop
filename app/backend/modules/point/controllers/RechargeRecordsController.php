<?php
/**
 * Created by PhpStorm.
 * User: king
 * Date: 2018/10/22
 * Time: 下午5:30
 */

namespace app\backend\modules\point\controllers;


use app\backend\modules\finance\services\PointService;
use app\backend\modules\point\models\RechargeModel;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use app\common\facades\Setting;

class RechargeRecordsController extends BaseController
{
    private $amount;

    public function index()
    {
        if (request()->ajax()) {
            return $this->successJson('ok', $this->getResultData());
        }
        return view('point.rechargeRecords');
    }

    /**
     * @return array
     */
    private function getResultData()
    {
        $recordsModels = $this->recordsModels()->toArray();
        $shopSet = Setting::get('shop.member');
        foreach ($recordsModels['data'] as &$item) {
            $item['member']['avatar'] =  $item['member']['avatar'] ? tomedia($item['member']['avatar'] ) : tomedia($shopSet['headimg']);
            $item['member']['nickname'] = $item['member']['nickname'] ?: '未更新';
        }
        return [
            'search'    => $this->searchParams(),
            'pageList'  => $recordsModels,
            'tab_list'     => PointService::getVueTags(),
            'amount' => $this->amount
        ];
    }

    /**
     * @return LengthAwarePaginator
     */
    private function recordsModels()
    {
        $recordsModels = RechargeModel::uniacid()->with('member');

        if ($search = $this->searchParams()) {
            $recordsModels = $recordsModels->search($search);
        }
        $this->amount  = $recordsModels->sum('money');
        return $recordsModels->orderBy('id', 'desc')->paginate();
    }

    /**
     * @param LengthAwarePaginator $recordsModels
     *
     * @return string
     */
    private function page(LengthAwarePaginator $recordsModels)
    {
        return PaginationHelper::show($recordsModels->total(), $recordsModels->currentPage(), $recordsModels->perPage());
    }

    /**
     * @return array
     */
    public function searchParams()
    {
        return request()->search ?: [];
    }
}
