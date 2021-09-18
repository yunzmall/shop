<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021-07-30
 * Time: 16:14
 */

namespace app\backend\modules\member\controllers;


use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\member\models\MemberUnique;
use app\common\components\BaseController;
use app\common\models\McMappingFans;
use app\common\models\Member;
use app\common\models\member\MemberCancel;
use app\common\models\member\MemberCancelSet;
use app\common\models\MemberAlipay;
use app\common\models\MemberMiniAppModel;
use app\common\models\Order;
use app\frontend\modules\member\models\MemberWechatModel;
use Illuminate\Support\Facades\DB;

class MemberCancelController extends BaseController
{
    public function index()
    {
        $form_data = request()->form;
        if ($form_data) {
            $set = MemberCancelSet::uniacid()->first();
            if (!$set) {
                $set = new MemberCancelSet();
            }
            $set->uniacid = \YunShop::app()->uniacid;
            $set->status = $form_data['status'];
            $set->tel_status = $form_data['tel_status'];
            $set->title = $form_data['title'];
            $set->content = $form_data['content'];
            if ($set->save()) {
                return $this->successJson('保存成功');
            } else {
                return $this->errorJson('保存失败');
            }
        }
        $set = MemberCancelSet::uniacid()->first();
        return view('member.memberCancel.index', [
            'set' => $set,
        ]);
    }

    public function verify()
    {
        return view('member.memberCancel.list');
    }

    public function search()
    {
        $search = request()->search;
        $list = MemberCancel::search($search)->orderby('created_at', 'desc')->paginate(15);
        return $this->successJson('ok', [
            'list' => $list,
        ]);
    }

    //通过
    public function pass()
    {
        $id = request()->id;
        if (!$id) {
            return $this->errorJson('请传入正确参数');
        }
        $model = MemberCancel::find($id);
        if (!$model) {
            return $this->errorJson('记录不存在');
        }
        $order = Order::where('uid', $model->member_id)->whereBetween('status', [0, 2])->first();
        if ($order) {
            return $this->errorJson('该会员存在交易中订单，暂不能审核！');
        }
        if ($this->delMember($model->member_id, $model)) {
            return $this->successJson('审核成功');
        } else {
            return $this->errorJson('审核失败');
        }
    }

    private function delMember($uid, $model)
    {
        $exception = DB::transaction(function () use ($uid, $model) {
            //公众号
            McMappingFans::where('uid', $uid)->delete();
            //小程序
            MemberMiniAppModel::where('member_id', $uid)->delete();
            //app
            MemberWechatModel::where('member_id', $uid)->delete();
            //统一
            MemberUnique::where('member_id', $uid)->delete();
            //支付宝
            MemberAlipay::where('member_id', $uid)->delete();
            Member::where('uid', $uid)->delete();  //删除mc_members数据
            MemberShopInfo::where('member_id', $uid)->delete();  //软删除yz_member
            $model->update(['status'=>2]);
        });
        setcookie('Yz-Token', '', time() - 3600);
        setcookie('Yz-appToken', '', time() - 3600);
        setcookie(session_name(), '',time() - 3600, '/');
        setcookie(session_name(), '',time() - 3600, '/addons/yun_shop');
        session_destroy();
        if (is_null($exception)) {
            return true;
        } else {
            return false;
        }
    }

    //驳回
    public function reject()
    {
        $id = request()->id;
        if (!$id) {
            return $this->errorJson('请传入正确参数');
        }
        $model = MemberCancel::find($id);
        if (!$model) {
            return $this->errorJson('记录不存在');
        }
        if ($model->update(['status'=>3])) {
            return $this->successJson('驳回成功');
        } else {
            return $this->errorJson('驳回失败');
        }
    }
}