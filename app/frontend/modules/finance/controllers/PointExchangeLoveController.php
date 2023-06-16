<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/10/28
 * Time: 13:37
 */

namespace app\frontend\modules\finance\controllers;


use app\common\components\ApiController;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\services\finance\PointService;
use app\frontend\models\Member;
use Illuminate\Support\Facades\DB;

class PointExchangeLoveController extends ApiController
{

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     * 积分手动转入爱心值
     */
    public function index()
    {
        $this->validate([
            'exchange_num' => 'required|numeric|min:0.01',
        ], null, [], [
            'exchange_num' => '转换积分',
        ]);

        $setting = Setting::get('point.set');
        $exchange_percent = bcdiv(floatval($setting['transfer_integral_love']) ?: 1, floatval($setting['transfer_integral']) ?: 1, 10);
        if (bccomp($exchange_percent, 0, 10) != 1) {
            return $this->errorJson('转换比例异常');
        }
        $point = bcadd(request()->exchange_num, 0, 2);
        $love = bcmul($point, $exchange_percent, 2);
        if (bccomp($love, 0, 2) != 1) {
            return $this->errorJson('转换的爱心值小于0.01');
        }
        $point_name = Setting::get('shop.shop.credit1') ?: '积分';
        $love_name = defined('LOVE_NAME') ? LOVE_NAME : '爱心值';

        if (bccomp(Member::current()->credit1, $point, 2) == -1) {
            return $this->errorJson('积分不足');
        }

        try {
            DB::beginTransaction();
            $data = [
                'point_income_type' => PointService::POINT_INCOME_LOSE,
                'member_id' => \YunShop::app()->getMemberId(),
                'point_mode' => PointService::POINT_MODE_POINT_EXCHANGE_LOVE,
                'point' => bcsub(0, $point, 2),
                'remark' => "{$point}{$point_name}手动转成{$love}{$love_name}"
            ];

            $pointService = new PointService($data);
            $res = $pointService->changePoint();
            if ($res === false) {
                throw new ShopException('扣除积分异常,未知错误');
            }

            $data = [
                'member_id' => \YunShop::app()->getMemberId(),
                'change_value' => $love,
                'operator' => 0,
                'operator_id' => 0,
                'relation' => '',
                'remark' => "{$point}{$point_name}手动转成{$love}{$love_name}",
            ];

            $res = (new \Yunshop\Love\Common\Services\LoveChangeService())->exchangePointToLove($data);
            if ($res !== true) {
                throw new ShopException('增加爱心值异常,未知错误');
            }
            DB::commit();
            return $this->successJson('转换成功');
        } catch (ShopException $e) {
            DB::rollBack();
            return $this->errorJson($e->getMessage());
        }

    }


}
