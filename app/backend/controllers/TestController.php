<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 18/04/2017
 * Time: 15:12
 */

namespace app\backend\controllers;

use app\backend\modules\charts\modules\member\services\LowerCountService;
use app\backend\modules\charts\modules\member\services\LowerOrderService;
use app\backend\modules\member\models\Member;
use app\backend\modules\order\models\OrderOperationLog;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\backend\modules\uploadVerificate\UploadVerificationController;
use app\common\components\BaseController;
use app\common\events\order\AfterOrderCreatedEvent;
use app\common\facades\EasyWeChat;
use app\common\facades\Setting;
use app\common\helpers\Url;
use app\common\models\Goods;
use app\common\models\Income;
use app\common\models\member\ChildrenOfMember;
use app\common\models\member\MemberParent;
use app\common\models\member\ParentOfMember;
use app\common\models\Order;
use app\common\models\OrderGoods;
use app\common\modules\shop\ShopConfig;
use app\common\services\PayFactory;
use app\common\services\Session;
use app\common\services\systemUpgrade;
use app\common\services\wechatApiV3\ApiV3Config;
use app\framework\Http\Request;
use app\framework\Redis\RedisServiceProvider;
use app\common\services\member\MemberRelation;
use app\common\services\MessageService;
use app\frontend\modules\member\models\SubMemberModel;
use app\Jobs\addReturnIncomeJob;
use app\Jobs\MemberLowerCountJob;
use app\Jobs\MemberLowerGroupOrderJob;
use app\Jobs\MemberLowerOrderJob;
use business\common\models\Staff;
use business\common\services\QyWechatRequestService;
use business\common\services\SettingService;
use Carbon\Carbon;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\TextCard;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Yunshop\BrowseFootprint\models\BrowseFootprintModel;
use Yunshop\CityDelivery\models\AnotherOrder;
use Yunshop\CityDelivery\models\DeliveryOrder;
use Yunshop\CityDelivery\services\SfService;
use Yunshop\ClockIn\api\ClockInPayController;
use Yunshop\Commission\models\AgentLevel;
use Yunshop\Commission\models\Agents;
use Yunshop\Commission\models\CommissionOrder;
use Yunshop\CustomerIncrease\models\Activity;
use Yunshop\CustomerIncrease\models\ActivityCode;
use Yunshop\CustomerIncrease\models\ActivityPoster;
use Yunshop\CustomerIncrease\services\IntroduceService;
use Yunshop\CustomerIncrease\services\PosterService;
use Yunshop\CustomerRadar\models\CustomerRadarSet;
use Yunshop\GroupDevelopUser\common\models\GroupChat;
use Yunshop\GroupDevelopUser\common\models\GroupCode;
use Yunshop\GroupDevelopUser\common\services\CodeService;
use Yunshop\GroupDevelopUser\common\services\GroupChatService;
use Yunshop\GroupReward\common\services\GroupChatRewardService;
use Yunshop\MemberTags\Common\models\MemberTagsRelationModel;
use Yunshop\ProjectManager\events\SaveNoticeEvent;
use Yunshop\StoreCashier\common\models\StoreSetting;
use Yunshop\TeamDividend\admin\models\MemberChild;
use Yunshop\UploadVerification\service\UploadVerificateRoute;
use app\backend\modules\member\models\MemberShopInfo;
use app\common\events\member\BecomeAgent;
use app\common\events\order\AfterOrderPaidEvent;
use app\common\models\order\OrderDeduction;
use Yunshop\StoreBusinessAlliance\models\StoreBusinessAlliancePriceDifferenceAwardModel;
use Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceRecommendAwardModel;
use app\common\events\order\AfterOrderReceivedEvent;
use Exception;
use Yunshop\WechatCustomers\common\models\Customer;
use Yunshop\YunChat\common\models\Chats;
use Yunshop\YunChat\common\service\ChatService;
use Yunshop\YunChat\common\utils\WechatKf;
use Yunshop\YunChat\manage\GroupController;


class TestController extends UploadVerificationBaseController
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;

    protected $isPublic = true;

    public function t()
    {
        return;
    }


    public function getParams($url)
    {
        $result = array();
        $mr     = preg_match('/groupId=([0-9]+)/', $url, $matchs);
        dd($url,$mr,$matchs);
    }

 //生成请求链接
    public function getRequestUrl($base_url, //设置页面的基础链接
                                  $app_id,   //设置页面的app_id
                                  $app_secret, //设置页面的app_secret
                                  $user_mobile, //会员的手机号
                                  $point = 0, //要增加的积分点数，如果设置页面没有开启积分增加开关则无效
                                  $notify_url = 'https://www.baidu.com' //要增加的积分点数，如果设置页面没有开启积分增加开关则无效
    )
    {
    }

    public function a()
    {
        (new \app\backend\modules\charts\modules\member\services\TimedTaskService())->handle();
    }

    private $amountItems;

    private function getAmountItems()
    {
        if (!isset($this->amountItems)) {
            $this->amountItems = \app\common\models\Withdraw::select(['member_id', DB::raw('sum(`actual_amounts`) as total_amount')])->
            where('type', 'Yunshop\Commission\models\CommissionOrder')
                ->where('status', 2)->groupBy('member_id')
                ->get();
        }
        return $this->amountItems;
    }

    private function getAmountByMemberId($memberId)
    {
        $amountItem = $this->getAmountItems()->where('member_id', $memberId)->first();
        if ($amountItem) {
            return $amountItem['total_amount'];
        }
        return 0;
    }

    public $orderId;

    /**
     * @return bool
     */
    public function index()
    {
        $a = Carbon::createFromTimestamp(1503488629)->diffInDays(Carbon::createFromTimestamp(1504069595), true);
        $member_relation = new MemberRelation();

        $relation = $member_relation->hasRelationOfParent(66, 5, 1);

        dd($relation);

        $text = '{"commission":{"title":"u5206u9500","data":{"0":{"title":"u5206u9500u4f63u91d1","value":"0.01u5143"},"2":{"title":"u4f63u91d1u6bd4u4f8b","value":"0.15%"},"3":{"title":"u7ed3u7b97u5929u6570","value":"0u5929"},"4":{"title":"u4f63u91d1u65b9u5f0f","value":"+u5546u54c1u72ecu7acbu4f63u91d1"},"5":{"title":"u5206u4f63u65f6u95f4","value":"2018-10-24 09:15:31"},"6":{"title":"u7ed3u7b97u65f6u95f4","value":"2018-10-24 09:20:04"}}},"order":{"title":"u8ba2u5355","data":[{"title":"u8ba2u5355u53f7","value":"SN201810 24091508a8"},{"title":"u72b6u6001","value":"u4ea4u6613u5b8cu6210"}]},"goods":{"title":"u5546u54c1","data":[[{"title":"u540du79f0","value":"u8700u9999u98ceu7fd4u8c46u82b1u6ce1u998du5e97"},{"title":"u91d1u989d","value":"4.00u5143"}]]}}';

        $pattern1 = '/\\\u[\d|\w]{4}/';
        preg_match($pattern1, $text, $exists);

        if (empty($exists)) {
            $pattern2 = '/(u[\d|\w]{4})/';
        }

    }

    public function op_database()
    {
        $sub_data = array(
            'member_id' => 999,
            'uniacid' => 5,
            'group_id' => 0,
            'level_id' => 0,
        );

        SubMemberModel::insertData($sub_data);

        if (SubMemberModel::insertData($sub_data)) {
            echo 'ok';
        } else {
            echo 'ko';
        }

    }

    public function notice()
    {
        $teamDividendNotice = \Setting::get('plugin.team_dividend');

        $member = Member::getMemberById(\YunShop::app()->getMemberId());

        if ($teamDividendNotice['template_id']) {
            $message = $teamDividendNotice['team_agent'];
            $message = str_replace('[昵称]', $member->nickname, $message);
            $message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
            $message = str_replace('[团队等级]', '一级', $message);

            $msg = [
                "first" => '您好',
                "keyword1" => "成为团队代理通知",
                "keyword2" => $message,
                "remark" => "",
            ];
            echo '<pre>';
            print_r($msg);
            MessageService::notice($teamDividendNotice['template_id'], $msg, 'oNnNJwqQwIWjAoYiYfdnfiPuFV9Y');

        }
        return;
    }

    public function fixImage()
    {
        $goods = DB::table('yz_goods')->get();
        $goods_success = 0;
        $goods_error = 0;
        foreach ($goods as $item) {

            if ($item['thumb'] && !preg_match('/^images/', $item['thumb'])) {

                $src = $item['thumb'];
                if (strexists($src, '/addons/') || strexists($src, 'yun_shop/') || strexists($src, '/static/')) {
                    continue;
                }

                if (preg_match('/\/images/', $item['thumb'])) {
                    $thumb = substr($item['thumb'], strpos($item['thumb'], 'images'));
                    $bool = DB::table('yz_goods')->where('id', $item['id'])->update(['thumb' => $thumb]);

                    if ($bool) {
                        $goods_success++;
                    } else {
                        $goods_error++;
                    }
                }
            }
        }


        $category = DB::table('yz_category')->get();
        $category_success = 0;
        $category_error = 0;
        foreach ($category as $item) {
            $src = $item['thumb'];
            if (strexists($src, 'addons/') || strexists($src, 'yun_shop/') || strexists($src, 'static/')) {
                continue;
            }

            if ($item['thumb'] && !preg_match('/^images/', $item['thumb'])) {
                if (preg_match('/\/images/', $item['thumb'])) {
                    $thumb = substr($item['thumb'], strpos($item['thumb'], 'images'));
                    $bool = DB::table('yz_category')->where('id', $item['id'])->update(['thumb' => $thumb]);
                    if ($bool) {
                        $category_success++;
                    } else {
                        $category_error++;
                    }
                }
            }
        }


        echo '商品图片修复成功：' . $goods_success . '个,失败：' . $goods_error . '个';
        echo '<br />';
        echo '分类图片修复成功：' . $category_success . '个，失败：' . $category_error . '个';

    }

    public function tt()
    {
        $member_relation = new MemberRelation();

        $member_relation->createParentOfMember();
    }

    public function fixIncome()
    {
        $count = 0;
        $income = Income::whereBetween('created_at', [1539792000, 1540915200])->get();
        foreach ($income as $value) {
            $pattern1 = '/\\\u[\d|\w]{4}/';
            preg_match($pattern1, $value->detail, $exists);
            if (empty($exists)) {
                $pattern2 = '/(u[\d|\w]{4})/';
                $value->detail = preg_replace($pattern2, '\\\$1', $value->detail);
                $value->save();
                $count++;
            }
        }
        echo "修复了{$count}条";
    }

    public function pp()
    {

        $member_info = Member::getAllMembersInfosByQueue(\YunShop::app()->uniacid);

        $total = $member_info->distinct()->count();

        dd($total);

        $this->chkSynRun(10);
        exit;

        /*$member_relation = new MemberRelation();

        $member_relation->createChildOfMember();*/
    }

    public function synRun($uniacid)
    {
        $parentMemberModle = new ParentOfMember();
        $childMemberModel = new ChildrenOfMember();
        $memberModel = new Member();
        $memberModel->_allNodes = collect([]);

        \Log::debug('--------------清空表数据------------');
        //$parentMemberModle->DeletedData();

        $memberInfo = $memberModel->getTreeAllNodes($uniacid);
        dd($memberInfo);
        if ($memberInfo->isEmpty()) {
            \Log::debug('----is empty-----');
            return;
        }

        foreach ($memberInfo as $item) {
            $memberModel->_allNodes->put($item->member_id, $item);
        }

        \Log::debug('--------queue synRun -----');
        dd(1);
        foreach ($memberInfo as $key => $val) {
            $attr = [];
            $child_attr = [];
            echo $val->member_id . '<BR>';
            \Log::debug('--------foreach start------', $val->member_id);
            $data = $memberModel->chktNodeParents($uniacid, $val->member_id);
            \Log::debug('--------foreach data------', $data->count());

            if (!$data->isEmpty()) {
                \Log::debug('--------insert init------');

                foreach ($data as $k => $v) {
                    $attr[] = [
                        'uniacid' => $uniacid,
                        'parent_id' => $k,
                        'level' => $v['depth'] + 1,
                        'member_id' => $val->member_id,
                        'created_at' => time()
                    ];

                    $child_attr[] = [
                        'uniacid' => $uniacid,
                        'parent_id' => $val->member_id,
                        'level' => $v['depth'] + 1,
                        'member_id' => $k,
                        'created_at' => time()
                    ];
                }

                $childMemberModel->createData($attr);
                /*
                 foreach ($data as $k => $v) {
                     $attr[] = [
                         'uniacid'   => $uniacid,
                         'parent_id'  => $k,
                         'level'     => $v['depth'] + 1,
                         'member_id' => $val->member_id,
                         'created_at' => time()
                     ];
                 }
                 $parentMemberModle->createData($attr);*/
            }
        }
    }

    public function synRun2($uniacid)
    {
        $childMemberModel = new ChildrenOfMember();
        $memberModel = new Member();
        $memberModel->_allNodes = collect([]);

        \Log::debug('--------------清空表数据------------');
        $childMemberModel->DeletedData();

        $memberInfo = $memberModel->getTreeAllNodes($uniacid);

        if ($memberInfo->isEmpty()) {
            \Log::debug('----is empty-----');
            return;
        }

        foreach ($memberInfo as $item) {
            $memberModel->_allNodes->put($item->member_id, $item);
        }

        \Log::debug('--------queue synRun -----');

        foreach ($memberInfo as $key => $val) {
            $attr = [];

            $memberModel->filter = [];
            echo '<pre>';
            print_r($val->member_id);
            \Log::debug('--------foreach start------', $val->member_id);
            $data = $memberModel->getDescendants($uniacid, $val->member_id);


            \Log::debug('--------foreach data------', $data->count());

            if (!$data->isEmpty()) {
                \Log::debug('--------insert init------');
                $data = $data->toArray();
                foreach ($data as $k => $v) {
                    if ($k != $val->member_id) {
                        $attr[] = [
                            'uniacid' => $uniacid,
                            'child_id' => $k,
                            'level' => $v['depth'] + 1,
                            'member_id' => $val->member_id,
                            'created_at' => time()
                        ];
                    } else {
                        $e = [$k, $v];
                    }
                }
                echo '<pre>';
                print_r($attr);
                //  $childMemberModel->createData($attr);
            }
        }
    }

    public function cmr()
    {
        $member_relation = new MemberRelation();

        $a = [
            [65, 79],
            [75, 79],
            [37, 65],
            [66, 65],
            [84, 75],
            [13, 37],
            [13090, 66],
            [24122, 66],
            [24132, 66],
            [91, 84],
            [9231, 84],
            [9571, 84],
            [89, 65]
        ];

        $aa = [
            [66, 0]
        ];

        foreach ($a as $item) {
            $member_relation->build($item[0], $item[1]);
        }
        echo 'ok';
    }

    public function chkSynRun()
    {
        //$uniacid = \YunShop::app()->uniacid;

        (new Member())->chkRelationData();


        /*$memberModel->_allNodes = collect([]);

        $memberInfo = $memberModel->getTreeAllNodes($uniacid);

        if ($memberInfo->isEmpty()) {
            \Log::debug('----is empty-----');
            return;
        }

        foreach ($memberInfo as $item) {
            $memberModel->_allNodes->put($item->member_id, $item);
        }

        \Log::debug('--------queue synRun -----');

        foreach ($memberInfo as $key => $val) {
            \Log::debug('--------foreach start------', $val->member_id);
            $memberModel->chkNodeParents($uniacid, $val->member_id);

        }

        echo 'end';*/
    }

    public function generateAddressJs()
    {
        $num = (new \app\common\services\address\GenerateAddressJs())->address();
        echo $num;
    }

    protected $GoodsGroupTable = 'yz_goods_group_goods';
    protected $DesignerTable = 'yz_designer';

    public function test()
    {

    }

    public function fixImg()
    {
        $time = [mktime(23, 59, 59, date('m'), date('d') - date('w') - 7, date('Y')), time()];
//        $time = [mktime(0,0,0,date('m'),1,date('Y')),time()];

        $order = Order::uniacid()->with(['hasManyOrderGoods.goods'])->whereBetween('create_time', $time)->get();
        foreach ($order as $item) {
            foreach ($item['hasManyOrderGoods'] as $it) {
                if (empty($it['thumb'])) {
                    $order_goods = OrderGoods::find($it['id']);
                    $data['thumb'] = yz_tomedia($it['goods']['thumb']);
                    $order_goods->fill($data);
                    $validator = $order_goods->validator();
                    if ($validator->fails()) {
                        throw new AppException($validator->messages()->first());
                    }
                    if (!$order_goods->save()) {
                        \Log::debug('修复图片失败,订单id:' . $item['id']);
                        throw new AppException('修复图片失败,订单id:' . $item['id']);
                    }
                }
            }

        }
        dd('修复成功');
    }

    public function suser()
    {
        $u = request()->input('u');

        $blank = request()->input('blank');
        if ($blank) {
            Session::set('member_id', $u);
        }

        $a = Session::get('member_id');
        dd($a);
    }
}
