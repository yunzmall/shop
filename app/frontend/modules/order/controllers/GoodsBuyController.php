<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/11
 * Time: 上午10:20
 */

namespace app\frontend\modules\order\controllers;

use app\common\components\ApiController;
use app\frontend\models\Member;
use app\frontend\modules\member\services\MemberCartService;
use app\frontend\modules\memberCart\MemberCartCollection;

class GoodsBuyController extends ApiController
{
    /**
     * @return MemberCartCollection
     * @throws \app\common\exceptions\AppException
     */
    protected function getMemberCarts()
    {
        $goods_params = [
            'goods_id' => request()->input('goods_id'),
            'total' => request()->input('total'),
            'option_id' => request()->input('option_id'),
        ];
        $result = new MemberCartCollection();
        $result->push(MemberCartService::newMemberCart($goods_params));

        event(new \app\common\events\goods\BeforeSaveGoodsVerify(request()->input('goods_id'), request()->input('total')));

        return $result;
    }

    /**
     * @throws \app\common\exceptions\ShopException
     */

    protected function validateParam()
    {

        $this->validate([
            'goods_id' => 'required|integer',
            'option_id' => 'integer',
            'total' => 'integer|min:1',
        ]);

    }

    /**
     * @return \Illuminate\Http\JsonResponse

     * @throws \app\common\exceptions\ShopException
     */
    public function index()
    {
        $this->validateParam();
        $trade = $this->getMemberCarts()->getTrade();

        return $this->successJson('成功', $trade);
    }

}