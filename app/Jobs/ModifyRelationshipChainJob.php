<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2021/2/26
 * Time: 15:20
 */

namespace app\Jobs;


use app\backend\modules\member\models\MemberRecord;
use app\common\events\member\RegisterByAgent;
use app\common\exceptions\AppException;
use app\common\models\member\ChildrenOfMember;
use app\common\models\member\ParentOfMember;
use app\common\models\MemberShopInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyRelationshipChainJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    const SUCCESS = 1;
    const FAIL = 2;

    private $uid;
    private $parent_id;
    private $record_id;
    private $member;
    private $parent;
    private $uniacid;
    private $before_parent;
    private $has_yz_agents = false;
    private $yz_team_dividend_agency = false;

    // 5分钟超时
    public $timeout = 300;


    public function __construct($uid,$parent_id,$record_id,$uniacid)
    {
        $this->queue = 'statistics';
        $this->uid = $uid;
        $this->parent_id = $parent_id;
        $this->record_id = $record_id;
        $this->uniacid = $uniacid;
    }

    public function handle()
    {
		\YunShop::app()->uniacid = \Setting::$uniqueAccountId = $this->uniacid;
		$start_time = time();
        \Log::debug('关系链修改开始',$this->record_id);
		$this->member = MemberShopInfo::where('member_id',$this->uid)->first();
		$this->parent = MemberShopInfo::where('member_id',$this->parent_id)->first();
		$this->before_parent = MemberShopInfo::where('member_id',$this->member->parent_id)->first();
        //获取所有子级
        $all_child = ChildrenOfMember::where('member_id',$this->uid)->get();
        //获取新父级的所有父级
        $all_new_parent = ParentOfMember::where('member_id',$this->parent_id)->get();
        DB::beginTransaction();
        try {
			//验证是否闭环关系链
			$chain = $all_new_parent->pluck('parent_id');
			$chain->push($this->uid);
			$chain->push($this->parent_id);
			if ($chain->count() != $chain->unique()->count()) {
				throw new AppException('关系链闭环，请检测关系链');
			}
        	//删除父表
            $this->deleteMemberParent($all_child);
            //删除子表
            $this->deleteMemberChildren($all_child);
            //重建父表
            $this->addMemberParent($all_new_parent, $all_child);
            //重建子表
            $this->addMemberChildren($all_new_parent, $all_child);
            //分销修改
            $this->hasYzAgentsTable();
            //经销商修改
            $this->hasYzTeamDividendAgency();
            //重新分配yz_member和yz_agent的relation
            $this->updateMemberRelation($all_child);
            //关系链修改状态
            $this->updateMemberRecord(static::SUCCESS,$start_time);
            DB::commit();
			\Log::debug('关系链修改完成',$this->record_id);
		} catch (\Exception $e) {
            // 关系链修改失败
            DB::rollBack();
            $this->updateMemberRecord(static::FAIL,$start_time);
            \Log::error('关系链修改失败',$e->getMessage());
        }
    }

    /**
     * 删除父表数据
     * @param $all_child
     * @throws \Exception
     * @author: Merlin
     * @Time: 2021/3/2   15:12
     */
    private function deleteMemberParent($all_child)
    {
		\Log::debug('关系链修改,删除父表开始',$this->record_id);
		//删除当前会员的所有父级
        ParentOfMember::where('member_id',$this->uid)->delete();
        //删除子级会员，级别大于当前会员的所有父级
        foreach ($all_child as $key=>$value) {
            ParentOfMember::where('member_id',$value['child_id'])->where('level','>',$value->level)->delete();
        }
		\Log::debug('关系链修改,删除父表完成',$this->record_id);

    }

    /**
     * 删除子表数据
     * @param $all_child
     * @throws \Exception
     * @author: Merlin
     * @Time: 2021/3/2   15:12
     */
    private function deleteMemberChildren($all_child)
    {
		\Log::debug('关系链修改,删除子表开始',$this->record_id);
		//删除以当前会员为子节点的所有数据
        ChildrenOfMember::where('child_id',$this->uid)->delete();
        //删除以当前会员的所有下级为子节点的所有数据
        foreach ($all_child as $key=>$value) {
            ChildrenOfMember::where('child_id',$value['child_id'])->where('level','>',$value->level)->delete();
        }
		\Log::debug('关系链修改,删除子表完成',$this->record_id);
	}

    /**
     * 重建父表数据
     * @param $all_new_parent
     * @param $all_child
     * @author: Merlin
     * @Time: 2021/3/2   15:12
     */
    private function addMemberParent($all_new_parent,$all_child)
    {
		\Log::debug('关系链修改,重建父表开始',$this->record_id);
		if ($this->parent_id == 0) {
            return;
        }
        $data = [];
        //遍历所有父级，重建当前会员父表
        foreach ($all_new_parent as $parent) {
            $data[] = [
                'uniacid'   => $this->member->uniacid,
                'parent_id' => $parent->parent_id,
                'level'     => $parent->level + 1,
                'member_id' => $this->uid,
                'created_at' => time()
            ];
        }
        //遍历所有父级，遍历当前会员所有子级，重建子级父表
        foreach ($all_new_parent as $parent) {
            foreach ($all_child as $child) {
                $data[] = [
                    'uniacid'   => $this->member->uniacid,
                    'parent_id' => $parent->parent_id,
                    //父级到当前会员层别+ 当前会员到子会员层级
                    'level'     => $parent->level + 1 + $child->level,
                    'member_id' => $child->child_id,
                    'created_at' => time()
                ];
            }
        }
        //遍历所有子级，重建所有子级相对于当前会员的父级的节点
        foreach ($all_child as $child) {
            $data[] = [
                'uniacid'   => $this->member->uniacid,
                'parent_id' => $this->parent_id,
                //父级到当前会员层别+ 当前会员到子会员层级
                'level'     =>  1 + $child->level,
                'member_id' => $child->child_id,
                'created_at' => time()
            ];
        }
        //当前会员对父级节点
        $data[] = [
            'uniacid'   => $this->member->uniacid,
            'parent_id' => $this->parent_id,
            'level'     => 1,
            'member_id' => $this->uid,
            'created_at' => time()
        ];
		foreach (array_chunk($data,10000) as $value) {
			ParentOfMember::insert($value);
		}
		\Log::debug('关系链修改,重建父表完成',$this->record_id);
	}

    /**
     * 重建子表数据
     * @param $all_new_parent
     * @param $all_child
     * @author: Merlin
     * @Time: 2021/3/2   15:13
     */
    private function addMemberChildren($all_new_parent,$all_child)
    {
		\Log::debug('关系链修改,重建子表开始',$this->record_id);
		if ($this->parent_id == 0) {
            return;
        }
        //遍历所有父级，重建所有父级对当前会员子表
        foreach ($all_new_parent as $parent) {
            $data[] = [
                'uniacid' => $this->member->uniacid,
                'child_id' => $this->uid,
                'level' => $parent->level + 1,
                'member_id' => $parent->parent_id,
                'created_at' => time()
            ];
        }
        //遍历所有父级，重建所有父级对当前会员所有子级的子表
        foreach ($all_new_parent as $parent) {
            foreach ($all_child as $child) {
                $data[] = [
                    'uniacid' => $this->member->uniacid,
                    'child_id' => $child->child_id,
                    //父级到当前会员层别+ 当前会员到子会员层级
                    'level' => $parent->level + 1 + $child->level,
                    'member_id' => $parent->parent_id,
                    'created_at' => time()
                ];
            }
        }
        //遍历所有子级，重建所有子级相对于当前会员的父级的节点
        foreach ($all_child as $child) {
            $data[] = [
                'uniacid' => $this->member->uniacid,
                'child_id' => $child->child_id,
                //父级到当前会员层别+ 当前会员到子会员层级
                'level' => 1 + $child->level,
                'member_id' => $this->parent_id,
                'created_at' => time()
            ];
        }
        //父级对当前会员节点
        $data[] = [
            'uniacid' => $this->member->uniacid,
            'child_id' => $this->uid,
            'level' => 1,
            'member_id' => $this->parent_id,
            'created_at' => time()
        ];
        foreach (array_chunk($data,10000) as $value) {
			ChildrenOfMember::insert($value);
		}
		\Log::debug('关系链修改,重建子表完成',$this->record_id);
	}

    /**
     * 更新会员及所有子级的relation
     * @param $all_child
     * @author: Merlin
     * @Time: 2021/3/2   15:13
     */
    private function updateMemberRelation($all_child)
    {
		\Log::debug('关系链修改,更新会员开始',$this->record_id);
		//只影响当前会员，当前会员下级，当前会员下下级
        $child_one = $all_child->where('level',1);
        $child_two = $all_child->where('level',2)->load(['hasOneChildYzMember']);
        //一级
        foreach ($child_one as $child) {
            $relation = $this->parent_id?implode(',',[$this->uid,(int)$this->parent_id,(int)$this->parent->parent_id]):implode(',',[$this->uid,(int)$this->parent_id]);
            MemberShopInfo::where('member_id',$child['child_id'])->update(['relation'=>$relation]);
            $this->updateCommission($child['child_id'],$relation,$this->uid);
            $this->updateTeamDividend($child['child_id'],$relation,$this->uid);
        }
        //二级
        foreach ($child_two as $child) {
            $relation = [$child->hasOneChildYzMember->parent_id,$this->uid,$this->parent_id];
            MemberShopInfo::where('member_id',$child['child_id'])->update(['relation'=>implode(',',$relation)]);
            $this->updateCommission($child['child_id'],$relation,$child->hasOneChildYzMember->parent_id);
            $this->updateTeamDividend($child['child_id'],$relation,$child->hasOneChildYzMember->parent_id);
        }
        if ($this->parent_id == 0) {
            $relation = [$this->parent_id];
        } elseif ($this->parent->parent_id == 0) {
            $relation = [$this->parent_id,$this->parent->parent_id];
        } else {
            $parent_parent_id = MemberShopInfo::where('member_id',$this->parent->parent_id)->value('parent_id');
            $relation = [$this->parent_id,$this->parent->parent_id,(int) $parent_parent_id];
        }
        $this->member->parent_id = $this->parent_id;
        $this->member->relation = implode(',',$relation);
        $this->member->inviter= 1;
        $this->member->save();
        $this->updateCommission($this->uid,$this->member->relation,$this->parent_id);
        $this->updateTeamDividend($this->uid,$this->member->relation,$this->parent_id);
        //触发监听
        $agent_data = [
            'member_id' => $this->uid,
            'parent_id' => $this->parent_id,
            'parent'    => $this->member->relation
        ];
        event(new RegisterByAgent($agent_data));
		\Log::debug('关系链修改,更新会员结束',$this->record_id);
	}

    /**
     * 修改关系链数据状态
     * @param $status
     * @param $start_time
     * @author: Merlin
     * @Time: 2021/3/2   15:13
     */
    private function updateMemberRecord($status,$start_time)
    {
        //总耗时
        $time = time() - $start_time;
        MemberRecord::where('id',$this->record_id)->update(['status'=>$status,'time'=>$time]);
    }

    /**
     * 是否有yz_agents表
     * @author: Merlin
     * @Time: 2021/3/2   15:14
     */
    private function hasYzAgentsTable()
    {
        //判断是否有yz_agent表，而不是判断插件有没有开启，防止插件关闭时修改关系链，开启后关系错误
        if (Schema::hasTable('yz_agents')) {
            $this->has_yz_agents = true;
        }
    }

    /**
     * 是否有yz_team_dividend_agency表
     * @author: Merlin
     * @Time: 2021/3/2   15:36
     */
    private function hasYzTeamDividendAgency()
    {
        //判断是否有yz_team_dividend_agency表，而不是判断插件有没有开启，防止插件关闭时修改关系链，开启后关系错误
        if (Schema::hasTable('yz_team_dividend_agency')) {
            $this->yz_team_dividend_agency = true;
        }
    }

    /**
     * 修改yz_agents的relation
     * @param $member_id
     * @param $parent_id
     * @param $relation
     * @author: Merlin
     * @Time: 2021/3/2   15:14
     */
    private function updateCommission($member_id,$relation,$parent_id)
    {
        if ($this->has_yz_agents) {
            DB::table('yz_agents')->where('member_id',$member_id)->update(['parent_id'=>$parent_id,'parent'=>$relation]);
        }
    }

    /**
     * 修改yz_team_dividend_agency的relation
     * @param $member_id
     * @param $parent_id
     * @param $relation
     * @author: Merlin
     * @Time: 2021/3/2   15:26
     */
    private function updateTeamDividend($member_id,$parent_id,$relation)
    {
        if ($this->yz_team_dividend_agency) {
            DB::table('yz_team_dividend_agency')->where('uid', $member_id)->update(['parent_id' => $parent_id, 'relation' => $relation]);
        }
    }

}