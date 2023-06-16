<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/12/13 下午2:25
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\backend\modules\finance\controllers;


use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\finance\models\BalanceRechargeRecords;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;


class BalanceRechargeRecordsController extends BaseController
{

    public function index()
    {
        return view('finance.balance.rechargeRecord')->render();
    }

    public function getData()
    {
        $records = BalanceRechargeRecords::records();

        $search = \YunShop::request()->search;
        if ($search) {
            $records = $records->search($search);
        }

        $amount = $records->sum('money');

        $recordList = $records->orderBy('created_at', 'desc')->paginate()->toArray();
        $shopSet = Setting::get('shop.member');

        foreach ($recordList['data'] as &$item) {
            if ($item['member']) {
                $item['member']['avatar'] = $item['member']['avatar'] ? tomedia($item['member']['avatar']) : tomedia($shopSet['headimg']);
                $item['member']['nickname'] = $item['member']['nickname'] ?:
                    ($item['member']['mobile'] ? substr($item['member']['mobile'], 0, 2) . '******' . substr($item['member']['mobile'], -2, 2) : '无昵称会员');
                $item['member']['yz_member']['level'] = $item['member']['yz_member']['level'] ?: '';
                $item['member']['yz_member']['group'] = $item['member']['yz_member']['group'] ?: '';
            } else {
                $item['member'] = [
                    'avatar' => tomedia($shopSet['headimg']),
                    'nickname' => '该会员已被删除或者已注销',
                    'yz_member' => [
                        'level' => '',
                        'group' => ''
                    ]
                ];
            }
        }

        //支付类型：1后台支付，2 微信支付 3 支付宝， 4 其他支付
        return $this->successJson('ok', [
            'shopSet'     => $shopSet,
            'recordList'  => $recordList,
            'memberGroup' => MemberGroup::getMemberGroupList(),
            'memberLevel' => MemberLevel::getMemberLevelList(),
            'payType'     => BalanceRechargeRecords::$typeComment,
            'search'      => $search,
            'amount'      => $amount,
        ]);
    }

    public function export()
    {

        $file_name = date('Ymdhis', time()) . '余额充值记录导出';
        $records = BalanceRechargeRecords::records();

        $search = \YunShop::request()->search;
        if ($search) {
            if (isset($search['time'])) {
                $search['time'] = explode(',', $search['time']);
                $search['time'] = [
                    'start' => $search['time'][0],
                    'end' => $search['time'][1]
                ];
            }
            $records = $records->search($search);
        }

        $list = $records->orderBy('created_at', 'desc')->get();

        $export_data[0] = ['充值单号', '粉丝', '会员ID','会员手机号', '会员等级', '会员分组', '充值时间', '充值方式', '充值金额', '状态', '备注信息'];


        foreach ($list as $key => $item) {
            switch ($item->status) {
                case 1:
                    $item->status = '充值成功';
                    break;
                case -1:
                    $item->status = '充值失败';
                    break;
                default:
                    $item->status = '申请中';
                    break;
            }
            $export_data[$key + 1] = [
                $item->ordersn,
                $item->member->nickname,
                $item->member->uid,
                $item->member->mobile,
                $item->member->yzMember->level->level_name,
                $item->member->yzMember->group->group_name,
                $item->created_at,
                $item->type_name,
                $item->money,
                $item->status,
                $item->remark,
            ];
        }

        \app\exports\ExcelService::fromArrayExport($export_data, $file_name.'.xlsx');

        // 商城更新，无法使用
//        \Excel::create($file_name, function ($excel) use ($export_data) {
//            // Set the title
//            $excel->setTitle('Office 2005 XLSX Document');
//
//            // Chain the setters
//            $excel->setCreator('芸众商城')
//                ->setLastModifiedBy("芸众商城")
//                ->setSubject("Office 2005 XLSX Test Document")
//                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
//                ->setKeywords("office 2005 openxml php")
//                ->setCategory("report file");
//
//            $excel->sheet('info', function ($sheet) use ($export_data) {
//                $sheet->rows($export_data);
//            });
//
//
//        })->export('xls');
    }

}
