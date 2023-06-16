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
use app\common\models\member\MemberChildren;
use app\common\models\member\MemberParent;
use app\common\models\member\ParentOfMemberBack;
use app\common\models\MemberShopInfo;
use app\common\models\Setting;
use app\common\services\member\MemberRelation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$uniacid = $this->argument('uniacid');
		\YunShop::app()->uniacid = \Setting::$uniqueAccountId = $uniacid;
        $this->info('重置关系链开始时间：'.Carbon::now()->toDateTimeLocalString());
        $this->process();
        $this->info('重置关系链结束时间：'.Carbon::now()->toDateTimeLocalString());
    }

	private function process()
	{
		set_time_limit(-1);
		ini_set('memory_limit',-1);
		$parent_list = MemberShopInfo::pluck('parent_id','member_id')->toArray();
		DB::beginTransaction();
		try {
			MemberParent::uniacid()->delete();
			MemberChildren::uniacid()->delete();
			foreach (array_chunk($parent_list,10000,true) as $parent) {
				foreach ($parent as $member_id => $parent_id) {
					$current_parent_id = $parent_id;
					$i = 1;
					while ($current_parent_id != 0) {
						$member_parent[] = [
							'member_id' => $member_id,
							'parent_id' => $current_parent_id,
							'level' => $i,
							'uniacid' => \YunShop::app()->uniacid,
						];
						$member_child[] = [
							'member_id' => $current_parent_id,
							'child_id' => $member_id,
							'level' => $i,
							'uniacid' => \YunShop::app()->uniacid,
						];
						$current_parent_id = $parent_list[$current_parent_id];
						$i++;
					}
				}
				foreach (array_chunk($member_parent,10000) as $value) {
					MemberParent::insert($value);
				}
				foreach (array_chunk($member_child,10000) as $value) {
					MemberChildren::insert($value);
				}
				unset($member_parent);
				unset($member_child);
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			$this->info('重置关系链失败：'.$e->getMessage());
		}
	}


}