<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/1 下午2:22
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 ****************************************************************/

namespace app\frontend\modules\finance\controllers;

use app\common\components\ApiController;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\services\finance\PointService;
use app\common\services\password\PasswordService;
use app\frontend\models\Member;
use Illuminate\Support\Facades\DB;
use Yunshop\Integral\Backend\Models\IntegralRechargeModel;
use Yunshop\Integral\Common\Services\IntegralChangeServer;

class PointTransferIntegralController extends ApiController
{
    private $set;//积分设置
    private $transfer;//转化者信息
    private $transfer_point;//转化积分

    public function preAction()
    {
        parent::preAction();

        $this->set = Setting::get('point.set');
        $this->transfer = Member::uniacid()->select('uid', 'avatar', 'nickname', 'realname', 'credit1')->where('uid', $this->memberId())->first();
        $this->transfer_point = trim(\YunShop::request()->transfer_point);//转化积分

        if (!app('plugins')->isEnabled('integral') || !$this->set['is_transfer_integral']) {
            throw new ShopException('未开启积分转化消费积分');
        };
    }

    private function needPassword()
    {
        return (new PasswordService())->isNeed('point', 'transfer');
    }

    protected function memberId()
    {
        return \YunShop::app()->getMemberId();
    }

    protected function password()
    {
        return request()->input('password');
    }

    //积分比例
    protected function transferPointRatio()
    {
        return (float)$this->set['transfer_point_ratio'] ?: 1;
    }

    //消费积分比例
    protected function transferIntegralRatio()
    {
        return (float)$this->set['transfer_integral_ratio'] ?: 1;
    }

    /**
     * 积分转化接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $result = $this->transferStart();
        return $result === true ? $this->successJson('转化成功') : $this->errorJson($result);
    }

    //页面预处理
    public function pagePer()
    {
        $integralName = app('plugins')->isEnabled('integral') ? \Yunshop\Integral\Common\Services\SetService::getIntegralName() : '消费积分';

        $data = [
            'credit1' => $this->transfer->credit1,//现积分
            'point_name' => Setting::get('shop.shop')['credit1'] ?: "积分",//积分自定义名称
            'integral_name' => $integralName,//消费积分自定义名称
            'transfer_point_ratio' => $this->transferPointRatio(),//积分比例
            'transfer_integral_ratio' => $this->transferIntegralRatio(),//消费积分比例
        ];

        return $this->successJson('ok', $data);
    }

    /**
     * 转化开始
     * @return bool|string
     */
    private function transferStart()
    {
//        if ($this->needPassword()) (new PasswordService())->checkPayPassword($this->memberId(), $this->password());
        $pointName=Setting::get('shop.shop')['credit1'] ?: "积分";  //积分自定义名称
        if (!$this->transfer) {
            return '未获取到会员信息';
        }
        if (bccomp($this->transfer_point, 0, 2) != 1) {
            return '转化'.$pointName.'必须大于 0.01';
        }
        if (bccomp($this->transfer->credit1, $this->transfer_point, 2) == -1) {
            return '转化'.$pointName.'不能大于您的剩余'.$pointName;
        }

        DB::beginTransaction();
        try {
            //转出积分
            (new PointService($this->getTransferRecordData()))->changePoint();
            //转入消费积分
            (new IntegralChangeServer())->universal($this->getRecipientRecordData($this->getTransferIntegral()));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }

    }

    private function getTransferRecordData()
    {
        return [
            'point_income_type' => PointService::POINT_INCOME_LOSE,
            'point_mode' => PointService::POINT_MODE_TRANSFER_INTEGRAL,
            'member_id' => $this->memberId(),
            'point' => -$this->transfer_point,
            'remark' => '积分转化消费积分-转出积分'
        ];
    }

    private function getRecipientRecordData($value)
    {
        $order_sn = (new IntegralRechargeModel())->createOrderSn('PTI');//积分转化消费积分
        return [
            'uid' => $this->memberId(),
            'uniacid' => \Yunshop::app()->uniacid,
            'change_value' => $value,
            'source_type' => 1,
            'source' => \Yunshop\Integral\Common\Services\ConstService::POINT_TRANSFER_INTEGRAL,
            'order_sn' => $order_sn,
            'remark' => "积分转化消费积分",
            'type' => 1,
        ];
    }

    //获取转化后的消费积分
    private function getTransferIntegral()
    {
        return bcmul(bcdiv($this->transferIntegralRatio(), $this->transferPointRatio(), 4), $this->transfer_point, 2);
    }

}
