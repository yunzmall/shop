<?php
/**
 * Created by PhpStorm.
 * User: king
 * Date: 2018/10/22
 * Time: 下午5:30
 */

namespace app\backend\modules\point\controllers;


use app\backend\modules\point\models\RechargeModel;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RechargeRecordsController extends BaseController
{
    public function index()
    {
        return view('point.rechargeRecords', $this->getResultData());
    }

    /**
     * @return array
     */
    private function getResultData()
    {
        $recordsModels = $this->recordsModels();

        return [
            'page'      => $this->page($recordsModels),
            'search'    => $this->searchParams(),
            'pageList'  => $recordsModels
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
