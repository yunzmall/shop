<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2020/9/10
 * Time: 6:19 PM
 */

namespace app\console\Commands;


use app\backend\modules\member\models\Member;
use app\common\models\member\ChildrenOfMemberBack;
use app\common\models\member\ParentOfMemberBack;
use app\common\services\member\MemberRelation;
use Illuminate\Console\Command;

class MemberRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member:release {uniacid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入会员关系';

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
        $this->info('=========start=========');
        $this->process();
        $this->info('=========end=========');
    }


    public function t1()
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
        $this->info('=========公众号=========' . $this->argument('uniacid'));

        $parentMemberModle = new ParentOfMemberBack();
        $childMemberModel = new ChildrenOfMemberBack();

        $parentMemberModle->DeletedData($this->argument('uniacid'));
        $childMemberModel->DeletedData($this->argument('uniacid'));

        $pageSize = 2000;
        $total = Member::getAllMembersInfosByQueue($this->argument('uniacid'))->distinct()->count();

        $total_page  = ceil($total/$pageSize);

        $this->info('------total-----' . $total);
        $this->info('------total_page-----' . $total_page);

        for ($curr_page = 1; $curr_page <= $total_page; $curr_page++) {
            $this->info('------curr_page-----' . $curr_page);
            \Log::info('--------record------', $curr_page);
            $offset      = ($curr_page - 1) * $pageSize;

            $this->synRun($this->argument('uniacid'), $parentMemberModle, $childMemberModel, $pageSize, $offset);
        }
    }

    /**
     * @param $uniacid
     */
    public function synRun($uniacid, $parentMemberModle, $childMemberModel, $pageSize, $offset)
    {
        $memberModel            = new Member();
        $memberModel->_allNodes = collect([]);

        $memberInfo = $memberModel->getTreeAllNodes($uniacid);

        if ($memberInfo->isEmpty()) {
            $this->info('----is empty-----');
            return;
        }

        foreach ($memberInfo as $item) {
            $memberModel->_allNodes->put($item->member_id, $item);
        }

        $member_info = Member::getAllMembersInfosByQueue($uniacid, $pageSize,
            $offset)->distinct()->get();
        $this->info('------queue member count-----' . $member_info->count());

        if (!$member_info->isEmpty()) {
            $this->info('-----queue member empty-----');
        }

        $this->info('--------queue synRun -----');

        foreach ($member_info as $key => $val) {
            $attr       = [];
            $child_attr = [];

            $this->info('--------foreach start------' . $val->member_id);
            $memberModel->filter = [];
            $data                = $memberModel->getNodeParents($uniacid, $val->member_id);

            if (!$data->isEmpty()) {
                $this->info('--------insert init------');

                foreach ($data as $k => $v) {
                    if ($k != $val->member_id) {
                        $attr[] = [
                            'uniacid'    => $uniacid,
                            'parent_id'  => $k,
                            'level'      => $v['depth'] + 1,
                            'member_id'  => $val->member_id,
                            'created_at' => time()
                        ];

                        $child_attr[] = [
                            'uniacid'    => $uniacid,
                            'child_id'   => $val->member_id,
                            'level'      => $v['depth'] + 1,
                            'member_id'  => $k,
                            'created_at' => time()
                        ];
                    } else {
                        file_put_contents(storage_path("logs/" . date('Y-m-d') . "_batchparent.log"),
                            print_r([$val->member_id, $v, 'insert'], 1), FILE_APPEND);
                    }
                }

                $parentMemberModle->createData($attr);
                $childMemberModel->createData($child_attr);
            }
        }
    }
}