<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/5/8 下午2:11
 * Email: livsyitian@163.com
 */

namespace app\frontend\modules\finance\controllers;


use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\helpers\ImageHelper;
use app\common\models\Income;
use app\common\services\popularize\PortType;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\frontend\models\MemberRelation;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\finance\factories\IncomePageFactory;
use app\frontend\modules\finance\services\ExtensionCenterService;
use app\frontend\modules\finance\services\PluginSettleService;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\services\MemberService;
use Yunshop\Designer\home\IndexController;
use Yunshop\HighLight\services\SetService;
use Yunshop\WithdrawalLimit\Common\models\MemberWithdrawalLimit;

class IncomePageController extends ApiController
{
    private $relationSet;
    private $is_agent;
    private $grand_total;
    private $usable_total;


    public function preAction()
    {
        parent::preAction();
        $this->relationSet = $this->getRelationSet();
    }

    /**
     * 收入页面接口
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function index(Request $request)
    {
        if (miniVersionCompare('1.1.115') && versionCompare('1.1.115')) {
            //版本符合
            if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
                //推广中心模版
                $view_set = \Yunshop\Decorate\models\DecorateTempletModel::getList(['is_default'=>1,'type'=>2],'*',false);
                if (empty($view_set) || $view_set->code == 'extension01') {
                    return $this->newIndex();
                }
            } else {
                return $this->newIndex();
            }
        }

        $this->dataIntegrated(['status' => 1, 'json' => ''],'template_set');
        $this->dataIntegrated($this->getIncomePage($request, true),'income_page');
        if(app('plugins')->isEnabled('designer'))
        {
            $this->dataIntegrated((new IndexController())->templateSet($request, true),'template_set');
        }
        if (app('plugins')->isEnabled('high-light') && SetService::getStatus()) {
            $this->dataIntegrated(\Yunshop\HighLight\services\WithdrawService::getHighLightUrl(),'high_light');
        }
        return $this->successJson('', $this->apiData);
    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function newIndex()
    {
        $this->dataIntegrated(['status' => 1, 'json' => ''],'template_set');
        ExtensionCenterService::init(request());
        $this->apiData['income_page'] = ExtensionCenterService::getIncomePage();
        return $this->successJson('', $this->apiData);
    }

    /**
     * 收入统计
     * @return \Illuminate\Http\JsonResponse
     */
    public function incomeStatistic()
    {
        ExtensionCenterService::init(request());
        $data = ExtensionCenterService::incomeStatistic();
        return $this->successJson('ok', $data);
    }

    /**
     * 收入统计图（动态、占比）
     * @return \Illuminate\Http\JsonResponse
     */
    public function incomeCharts()
    {
        ExtensionCenterService::init(request());
        if (!empty(request()->charts_type) && request()->charts_type == 1) {
            $data = ExtensionCenterService::incomeProportion();
        } else {
            $data = ExtensionCenterService::incomeDynamic();
        }
        return $this->successJson('ok', $data);
    }

    /**
     * 粉丝数据统计图（裂变、转化）
     * @return \Illuminate\Http\JsonResponse
     */
    public function fansCharts()
    {
        ExtensionCenterService::init(request());
        if (!empty(request()->charts_type) && request()->charts_type == 1) {
            $data = ExtensionCenterService::fansConversion();
        } else {
            $data = ExtensionCenterService::fansFission();
        }
        return $this->successJson('ok', $data);
    }

    /**
     * 推广订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function extension()
    {
        ExtensionCenterService::init(request());
        if (!empty(request()->extension_type) && request()->extension_type == 1) {
            $data = ExtensionCenterService::extensionFans();
        } else {
            $data = ExtensionCenterService::extensionOrder();
        }
        return $this->successJson('ok',$data);
    }

    /**
     * @param $request
     * @param null $integrated
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function getIncomePage(Request $request, $integrated = null)
    {
        //检测是否推广员
        $this->is_agent = $this->isAgent();

        //不是推广员且有设置跳转链接时
        $relation_set = \Setting::get('member.relation');
        $extension_set = \Setting::get('popularize.mini');
        $jump_link = '';
        $small_jump_link = '';
        $small_extension_link = '';
        if ($relation_set['is_jump'] && !empty($relation_set['jump_link'])) {
            if (!$this->is_agent) {
                $jump_link = $relation_set['jump_link'];
                $small_jump_link = $relation_set['small_jump_link'];
                $small_extension_link = $extension_set['small_extension_link'];
                if(is_null($integrated)){
                    return $this->successJson('ok', ['jump_link' => $jump_link,'small_jump_link'=>$small_jump_link,'small_extension_link'=>$small_extension_link]);
                }else{
                    return show_json(1,['jump_link' => $jump_link,'small_jump_link'=>$small_jump_link,'small_extension_link'=>$small_extension_link]);
                }
            }
        }

        list($available, $unavailable) = $this->getIncomeInfo();

        //添加商城营业额
        $is_show_performance = OrderAllController::isShow();

        //更多权限是否显示
        $is_show_unable = PortType::isShowUnable(\YunShop::request()->type);

        //提现额度
        $withdraw_limit = [
            'is_show' => false
        ];
        if(app('plugins')->isEnabled('withdrawal-limit'))
        {
            $limit_set = array_pluck(\Setting::getAllByGroup('withdrawal-limit')->toArray(), 'value', 'key');
            if($limit_set['is_open'] == 1 && $limit_set['is_show'] == 1)
            {
                $memberModel = MemberWithdrawalLimit::uniacid()->where('member_id',\YunShop::app()->getMemberId())->first();
                if($memberModel){
                    $limit = $memberModel->total_amount;
                }else{
                    $limit = 0;
                }
                $withdraw_limit = [
                    'is_show' => true,
                    'amount' => $limit
                ];
            }
        }


        $data = [
            'info' => $this->getPageInfo(),
            'parameter' => $this->getParameter(),
            'available' => $available,
            'unavailable' => $unavailable,
            'is_show_performance' => $is_show_performance,
            'jump_link' => $jump_link,
            'small_jump_link' => $small_jump_link,
            'small_extension_link' => $small_extension_link,
            'is_show_unable' => $is_show_unable,
            'withdraw_limit' => $withdraw_limit,
            'withdraw_date' => $this->getWithdrawDate(),
            'show_member_id' => PortType::showMemberId(\YunShop::request()->type),
        ];
        if(is_null($integrated)){
            return $this->successJson('ok', $data);
        }else{
            return show_json(1,$data);
        }
    }

    /**
     * 页面信息
     *
     * @return array
     */
    private function getPageInfo()
    {
        $autoWithdraw = 0;
        if (app('plugins')->isEnabled('mryt')) {
            $uid = \YunShop::app()->getMemberId();
            $autoWithdraw = (new \Yunshop\Mryt\services\AutoWithdrawService())->isWithdraw($uid);
        }

        if (app('plugins')->isEnabled('team-dividend')) {
            $uid = \YunShop::app()->getMemberId();
            $autoWithdraw = (new \Yunshop\TeamDividend\services\AutoWithdrawService())->isWithdraw($uid);
        }

        $member_id = \YunShop::app()->getMemberId();

        $memberModel = Member::select('nickname', 'avatar', 'uid')->whereUid($member_id)->first();

        //IOS时，把微信头像url改为https前缀
        $avatar = ImageHelper::iosWechatAvatar($memberModel->avatar);
        return [
            'avatar' => $avatar,
            'nickname' => $memberModel->nickname,
            'member_id' => $memberModel->uid,
            'grand_total' => $this->grand_total,
            'usable_total' => $this->usable_total,
            'auto_withdraw' => $autoWithdraw,
        ];
    }


    private function getParameter()
    {
        return [
            'share_page' => $this->getSharePageStatus(),
            'plugin_settle_show' => PluginSettleService::doesIsShow(),  //领取收益 开关是否显示
        ];
    }


    /**
     * 收入信息
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    private function getIncomeInfo()
    {
        $lang_set = $this->getLangSet();

        $is_relation = $this->isOpenRelation();

        $config = $this->getIncomePageConfig();

        $total_income = $this->getTotalIncome();

        //是否显示推广插件入口
        $popularize_set = PortType::popularizeSet(\YunShop::request()->type);

        $available = [];
        $unavailable = [];
        foreach ($config as $key => $item) {

            $incomeFactory = new IncomePageFactory(new $item['class'], $lang_set, $is_relation, $this->is_agent, $total_income,$key);

            if (!$incomeFactory->isShow()) {
                continue;
            }

            //不显示
            if (in_array($incomeFactory->getAppUrl(), $popularize_set)) {
                continue;
            }

            $income_data = $incomeFactory->getIncomeData();

            if ($incomeFactory->isAvailable()) {
                $available[] = $income_data;
            } else {
                $unavailable[] = $income_data;
            }

            //unset($incomeFactory);
            //unset($income_data);
        }

        return [$available, $unavailable];
    }


    /**
     * 获取商城中的插件名称自定义设置
     *
     * @return mixed
     */
    private function getLangSet()
    {
        $lang = \Setting::get('shop.lang', ['lang' => 'zh_cn']);

        return $lang[$lang['lang']];
    }

    private function getWithdrawDate()
    {
        $income_set = \Setting::get('withdraw.income');
        $withdraw_date = [
            'day' => 0, //可提现日期
            'disable' => 0 //是否禁用
        ];
        $day_msg = '无提现限制';
        if (is_array($income_set['withdraw_date'])) {
            $day = date('d');
            $day_msg = '可提现日期为：'.implode(',',$income_set['withdraw_date']).'号';
            $withdraw_date = [
                'day' => min($income_set['withdraw_date']),
                'disable' => 1
            ];
            foreach ($income_set['withdraw_date'] as $date) {
                if ($day == $date) {
                    $withdraw_date = [
                        'day' => $date,
                        'disable' => 0,
                    ];
                    break;
                }
                if ($day < $date) {
                    $withdraw_date = [
                        'day' => $date,
                        'disable' => 1,
                    ];
                }


            }
        }
        $withdraw_date['day_msg'] = $day_msg;
        return $withdraw_date;
    }


    /**
     * 是否开启关系链 todo 应该提出一个公用的服务
     *
     * @return bool
     */
    private function isOpenRelation()
    {
        if (!is_null($this->relationSet) && 1 == $this->relationSet->status) {
            return true;
        }
        return false;
    }


    private function getSharePageStatus()
    {
        if (!is_null($this->relationSet) && 1 == $this->relationSet->share_page) {
            return true;
        }
        return false;
    }


    private function getRelationSet()
    {
        return MemberRelation::uniacid()->first();
    }

    private function getTotalIncome()
    {
        $incomeConfig = \app\backend\modules\income\Income::current()->getItems();

        $incomeConfig = collect($incomeConfig)->pluck('class')->toArray();

        $total_income =Income::uniacid()->selectRaw('member_id, incometable_type, sum(amount) as total_amount, sum(if(status = 0, amount, 0)) as usable_total')
            ->whereIn('incometable_type', $incomeConfig)
            ->whereMember_id(\YunShop::app()->getMemberId())
            ->groupBy('incometable_type', 'member_id')
            ->get();

        //计算累计收入
        $this->grand_total = sprintf("%.2f",$total_income->sum('total_amount'));
        $this->usable_total = sprintf("%.2f",$total_income->sum('usable_total'));
        return $total_income;
    }


    /**
     * 登陆会员是否是推客
     *
     * @return bool
     */
    private function isAgent()
    {
        return MemberModel::isAgent();
    }


    /**
     * 收入页面配置 config
     *
     * @return mixed
     */
    private function getIncomePageConfig()
    {
        return \app\backend\modules\income\Income::current()->getPageItems();
    }


    //累计收入
    private function getGrandTotal()
    {
        return $this->getIncomeModel()->sum('amount');
    }

    //可提现收入
    private function getUsableTotal()
    {
        return $this->getIncomeModel()->where('status', 0)->sum('amount');
    }


    private function getIncomeModel()
    {
        $member_id = \YunShop::app()->getMemberId();

        return Income::uniacid()->where('member_id',$member_id);
    }

}
