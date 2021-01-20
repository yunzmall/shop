<?php
/**
 * Created by PhpStorm.
 * Date: 2020/10/13
 * Time: 9:35 PM
 */

namespace app\console\Commands;


use app\backend\modules\member\models\Member;
use app\common\models\member\ChildrenOfMember;
use app\common\models\member\ParentOfMember;
use app\common\models\MemberShopInfo;
use app\common\services\member\MemberRelation;
use Illuminate\Console\Command;

class FixMemberRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:release {uniacid} {member_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复指定会员关系';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $account = \app\common\models\AccountWechats::getAccountByUniacid($this->argument('uniacid'));

        \YunShop::app()->uniacid = $this->argument('uniacid');
        \YunShop::app()->weid = $this->argument('uniacid');
        \YunShop::app()->acid = $this->argument('uniacid');
        \YunShop::app()->account = $account ? $account->toArray() : '';

        $this->info('=========start=========');
        $this->process();
        $this->info('=========end=========');
    }


    public function t()
    {
        $bar = $this->output->createProgressBar(1000);
        $bar->setFormat("   %elapsed:6s%/%estimated:-6s%   内存消耗: %memory:6s%\n%current%/%max% [%bar%] %percent:3s%%");
        for ($i=1; $i<=1000; $i++){
            usleep(5000);
            $bar->advance();
        }
        $bar->finish();
        echo "\n";
    }

    public function process()
    {
        $parentMemberModle      = new ParentOfMember();
        $childMemberModel       = new ChildrenOfMember();
        $memberModel            = new Member();
        $memberRelation          = new MemberRelation();
        $memberModel->_allNodes = collect([]);

        $memberInfo = $memberModel->getTreeAllNodes($this->argument('uniacid'));

        if ($memberInfo->isEmpty()) {
            $this->info('----is empty-----');
            return;
        }

        foreach ($memberInfo as $item) {
            $memberModel->_allNodes->put($item->member_id, $item);
        }

        //获取指定会员所有下级
        $data = $memberModel->getDescendants($this->argument('uniacid'), $this->argument('member_id'));

        if (!$data->isEmpty()) {
            //导入关系链
            foreach ($data as $child_member_id => $val) {
                $attr       = [];
                $child_attr = [];

                $this->info('--------foreach start------' . $child_member_id);
                $memberModel->filter = [];
                $nodeParents                = $memberModel->getNodeParents($this->argument('uniacid'), $child_member_id);

                if (!$nodeParents->isEmpty()) {
                    $this->info('--------insert init------');

                    foreach ($nodeParents as $parent_member_id => $v) {
                        $memberRelation->delMemberOfRelation($child_member_id, $parent_member_id);

                        if ($parent_member_id != $child_member_id) {
                            $attr[] = [
                                'uniacid'    => $this->argument('uniacid'),
                                'parent_id'  => $parent_member_id,
                                'level'      => $v['depth'] + 1,
                                'member_id'  => $child_member_id,
                                'created_at' => time()
                            ];

                            $child_attr[] = [
                                'uniacid'    => $this->argument('uniacid'),
                                'child_id'   => $child_member_id,
                                'level'      => $v['depth'] + 1,
                                'member_id'  => $parent_member_id,
                                'created_at' => time()
                            ];
                        } else {
                            $this->info('--------fail------' . $parent_member_id);
                        }
                    }

                    $parentMemberModle->createData($attr);
                    $childMemberModel->createData($child_attr);
                }
            }
        }
    }
}