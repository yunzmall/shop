<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/2
 * Time: 下午1:55
 */

namespace app\backend\modules\member\models;

use app\backend\modules\member\observers\MemberObserver;
use app\backend\modules\order\models\Order;
use app\common\facades\Setting;
use app\common\models\member\MemberDel;
use app\common\traits\MemberTreeTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Member extends \app\common\models\Member
{
    use MemberTreeTrait;

    public $widgets = [];

    static protected $needLog = true;

    /**
     * 在boot()方法里注册下模型观察类
     * boot()和observe()方法都是从Model类继承来的
     * 主要是observe()来注册模型观察类，可以用TestMember::observe(new TestMemberObserve())
     * 并放在代码逻辑其他地方如路由都行，这里放在这个TestMember Model的boot()方法里自启动。
     */
    public static function boot()
    {
        parent::boot();
        //注册观察者
        static::observe(new MemberObserver);
//
//        static::addGlobalScope(function (Builder $builder) {
//            $builder->isPlugin();
//        });
    }
    /**
     * 删除会员信息
     *
     * @param $id
     */
    public static function deleteMemberInfoById($id)
    {
        return self::uniacid()
            ->where('uid', $id)
            ->delete();
    }

    /**
     * 更新删除会员信息
     *
     * @param $id
     */
    public static function UpdateDeleteMemberInfoById($id)
    {
        $member_model = Member::find($id);
        MemberDel::insertData($member_model);
        $member_model->email = '';
        $member_model->nickname = '';
        $member_model->mobile = '';
        $member_model->email = '';
        $member_model->gender = 0;
        $member_model->nationality = '';
        $member_model->resideprovince = '';
        $member_model->residecity = '';
        $member_model->salt = '';
        $member_model->password = '';
        //todo 由于关系链下列参数不能清空
        /*$member_model->createtime = 0;
        $member_model->avatar = '';
        $member_model->credit1 = 0;
        $member_model->credit2 = 0;
        $member_model->credit3 = 0;
        $member_model->credit4 = 0;
        $member_model->credit5 = 0;
        $member_model->credit6 = 0;*/
        if ($member_model->save()) {
            return $member_model->uid;
        } else {
            return false;
        }
    }

    public function address()
    {
        return $this->hasMany('app\backend\modules\member\models\MemberAddress', 'uid', 'uid');
    }

    /**
     * @param $keyWord
     *
     */
    public static function getMemberByName($keyWord)
    {
        return self::uniacid()
            ->searchLike($keyWord)
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with('yzMember')
            ->with('hasOneFans')
            ->with('hasOneMiniApp')
            ->get();
    }
    public static function getMemberJsonByName($keyWord)
    {
        return self::uniacid()
            ->searchLike($keyWord)
            ->select('uid','nickname','avatar','mobile')
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['hasOneFans'=>function($q){
                return $q->select('openid','uid');
            }])
            ->get();
    }


    /**
     * 获取会员列表
     *
     * @return mixed
     */
    public static function getMembers()
    {
        return self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2'])
            ->uniacid()
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['yzMember' => function ($query) {
                return $query->select(['member_id', 'parent_id', 'is_agent', 'group_id', 'level_id', 'is_black'])->uniacid()
                    ->with(['group' => function ($query1) {
                        return $query1->select(['id', 'group_name'])->uniacid();
                    }, 'level' => function ($query2) {
                        return $query2->select(['id', 'level', 'level_name'])->uniacid();
                    }, 'agent' => function ($query3) {
                        return $query3->select(['uid', 'avatar', 'nickname'])->uniacid();
                    }]);
            }, 'hasOneFans' => function ($query4) {
                return $query4->select(['uid', 'openid', 'follow as followed'])->uniacid();
            }, 'hasOneOrder' => function ($query5) {
                return $query5->selectRaw('uid, count(uid) as total, sum(price) as sum')
                    ->uniacid()
                    ->where('status', 3)
                    ->groupBy('uid');
            }])
            ->orderBy('uid', 'desc');
    }

    /**
     * 获取会员信息
     *
     * @param $id
     * @return mixed
     */
    public static function getMemberInfoById($id)
    {
        return self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2'])
            ->uniacid()
            ->where('uid', $id)
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['yzMember' => function ($query) {
                return $query->select(['member_id', 'parent_id', 'is_agent', 'group_id', 'level_id', 'is_black', 'alipayname', 'alipay', 'content', 'status', 'custom_value', 'validity', 'member_form', 'withdraw_mobile','wechat'])->where('is_black', 0)
                    ->with(['group' => function ($query1) {
                        return $query1->select(['id', 'group_name']);
                    }, 'level' => function ($query2) {
                        return $query2->select(['id', 'level', 'level_name']);
                    }, 'agent' => function ($query3) {
                        return $query3->select(['uid', 'avatar', 'nickname']);
                    }]);
            }, 'hasOneFans' => function ($query2) {
                return $query2->select(['uid', 'follow as followed']);
            }, 'hasOneOrder' => function ($query5) {
                return $query5->selectRaw('uid, count(uid) as total, sum(price) as sum')
                    ->uniacid()
                    ->where('status', 3)
                    ->groupBy('uid');
            }
            ])
            ->first();
    }

    /**
     * 获取会员信息（不判断黑名单）
     * @param $id
     * @return mixed
     */
    public static function getMemberInfoBlackById($id)
    {
        return self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2'])
            ->uniacid()
            ->where('uid', $id)
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['yzMember' => function ($query) {
                return $query->select(['member_id', 'parent_id', 'is_agent', 'group_id', 'level_id', 'is_black', 'alipayname', 'alipay', 'content', 'status', 'custom_value', 'validity', 'member_form', 'withdraw_mobile','wechat','invite_code'])
                    ->with(['group' => function ($query1) {
                        return $query1->select(['id', 'group_name']);
                    }, 'level' => function ($query2) {
                        return $query2->select(['id', 'level', 'level_name']);
                    }, 'agent' => function ($query3) {
                        return $query3->select(['uid', 'avatar', 'nickname']);
                    }]);
            }, 'hasOneFans' => function ($query2) {
                return $query2->select(['uid', 'follow as followed']);
            }, 'hasOneOrder' => function ($query5) {
                return $query5->selectRaw('uid, count(uid) as total, sum(price) as sum')
                    ->uniacid()
                    ->where('status', 3)
                    ->groupBy('uid');
            }
            ])
            ->first();
    }

    /**
     * 获取会员基本信息
     *
     * @param $id
     * @return mixed
     */
    public static function getMemberBaseInfoById($id)
    {
        return self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2'])
            ->uniacid()
            ->where('uid', $id)
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['yzMember' => function ($query) {
                return $query->select(['member_id', 'parent_id', 'is_agent', 'group_id', 'level_id', 'is_black', 'alipayname', 'alipay', 'content', 'status', 'custom_value', 'validity', 'member_form', 'withdraw_mobile','wechat'])->where('is_black', 0)
                    ->with(['group' => function ($query1) {
                        return $query1->select(['id', 'group_name']);
                    }, 'level' => function ($query2) {
                        return $query2->select(['id', 'level', 'level_name']);
                    }, 'agent' => function ($query3) {
                        return $query3->select(['uid', 'avatar', 'nickname']);
                    }]);
            }, 'hasOneFans' => function ($query2) {
                return $query2->select(['uid', 'follow as followed', 'unionid']);
            }
            ])
            ->first();
    }

    /**
     * 更新会员信息
     *
     * @param $data
     * @param $id
     * @return mixed
     */
    public static function updateMemberInfoById($data, $id)
    {
        return self::uniacid()
            ->where('uid', $id)
            ->update($data);
    }



    /**
     * 检索会员信息
     *
     * @param $parame
     * @return mixed
     */
    public static function searchMembers($parame, $credit = null)
    {
        if (!isset($credit)) {
            $credit = 'credit2';
        }
        $result = self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2', 'is_old', 'mark_member_id'])
            ->uniacid()->leftJoin('yz_member_del_log', 'mc_members.uid', '=', 'yz_member_del_log.member_id')
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id');

        if (!empty($parame['search']['mid'])) {
            $result = $result->where('uid', $parame['search']['mid']);
        }
        if (isset($parame['search']['searchtime']) && $parame['search']['searchtime'] == 1) {
            if ($parame['search']['times']['start'] != '请选择' && $parame['search']['times']['end'] != '请选择') {
                $range = [strtotime($parame['search']['times']['start']), strtotime($parame['search']['times']['end'])];
                $result = $result->whereBetween('createtime', $range);
            }
        }

        if (!empty($parame['search']['realname'])) {
            $result->where(function ($query) use ($parame) {
                switch ($parame['search']['name_type']) {
                    case 1 :
                        $query->where('realname', 'like', '%'.$parame['search']['realname'] . '%');
                        break;
                    case 2 :
                        $query->where('mobile', 'like', '%'.$parame['search']['realname'] . '%');
                        break;
                    default :
                        $query->searchLike($parame['search']['realname']);
                        break;
                }
            });
        }

        if($parame['search']['custom_value']){
            $result->where('yz_member.custom_value', 'like', '%' . $parame['search']['custom_value'] . '%');
        }

        if (!empty($parame['search']['groupid'])) {
            $result->where('yz_member.group_id', $parame['search']['groupid']);
        }

        if (is_numeric($parame['search']['level'])) {
            $result->where('yz_member.level_id', $parame['search']['level']);
        }

        if ($parame['search']['isblack'] != '') {
            $result->where('yz_member.is_black', $parame['search']['isblack']);
        }

        if ($parame['search']['isagent'] != '') {
            $result->where('yz_member.is_agent', $parame['search']['isagent']);
        }

        //余额区间搜索
        if ($parame['search']['min_credit2']) {
            $result = $result->where($credit, '>', $parame['search']['min_credit2']);
        }
        if ($parame['search']['max_credit2']) {
            $result = $result->where($credit, '<', $parame['search']['max_credit2']);
        }

        if ($parame['search']['followed'] != '') {
            $result = $result->whereHas('hasOneFans', function ($q2) use ($parame) {
                $q2->where('follow', $parame['search']['followed']);
            });
        }


        $result = $result->with(['yzMember' => function ($query) {
            return $query->select(['member_id', 'parent_id', 'inviter', 'is_agent', 'group_id', 'level_id', 'is_black', 'withdraw_mobile', 'is_old'])
                ->with(['group' => function ($query1) {
                    return $query1->select(['id', 'group_name'])->uniacid();
                }, 'level' => function ($query2) {
                    return $query2->select(['id', 'level_name'])->uniacid();
                }, 'agent' => function ($query3) {
                    return $query3->select(['uid', 'avatar', 'nickname','mobile'])->uniacid();
                }]);
        }, 'hasOneFans' => function ($query4) {
            return $query4->select(['uid', 'follow as followed'])->uniacid();
        }, 'hasOneOrder' => function ($query5) {
            return $query5->selectRaw('uid, count(uid) as total, sum(price) as sum')
                ->uniacid()
                ->where('status', Order::COMPLETE)
                ->groupBy('uid');
        },'hasOneMiniApp', 'hasOneUnique', 'hasOneWechat', 'hasOneDouyin','yzMember.agent']);

        //判断支付宝插件
        if (file_exists(base_path('plugins/alipay-onekey-login'))) {
            $result->with('hasOneAlipay');
        }

        //判断会员标签插件
        $set = array_pluck(Setting::getAllByGroup('member-tags')->toArray(), 'value', 'key');
        if (app('plugins')->isEnabled('member-tags') && $set['is_open']) {
            if ($parame['tag_id'])
            {
                $result->whereHas('hasManyTag', function($query) use($parame){
                    $query->where('tag_id', $parame['tag_id']);
                });
            }
            $result->with(['hasManyTag' => function($query){
                $query->with('tag');
            }]);
        }
	
	    if (app('plugins')->isEnabled('wechat-customers') && app('plugins')->isEnabled('work-wechat-platform')) {
		    $result->with('hasOneCustomers');
	    }

        $result->whereNull('yz_member_del_log.member_id')
            ->whereNull('yz_member.deleted_at')
            ->orderBy('yz_member.member_id', 'desc');

        return $result;
    }

    public function getTagsList(){
        return MemberTagsModel::select('id','title')->where('type', 1)->get()->toArray();
    }

    /**
     * 检索会员信息(财务--会员余额页面使用)
     *
     * @param $parame
     * @return mixed
     */
    public static function searchMembersBlance($parame, $credit = null)
    {
        if (!isset($credit)) {
            $credit = 'credit2';
        }
        $result = self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2', 'is_old', 'mark_member_id'])
            ->uniacid()->leftJoin('yz_member_del_log', 'mc_members.uid', '=', 'yz_member_del_log.member_id')
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id');

        if (!empty($parame['search']['mid'])) {
            $result = $result->where('uid', $parame['search']['mid']);
        }
        if (isset($parame['search']['searchtime']) && $parame['search']['searchtime'] == 1) {
            if ($parame['search']['times']['start'] != '请选择' && $parame['search']['times']['end'] != '请选择') {
                $range = [strtotime($parame['search']['times']['start']), strtotime($parame['search']['times']['end'])];
                $result = $result->whereBetween('createtime', $range);
            }
        }

        if (!empty($parame['search']['realname'])) {
            $result = $result->where(function ($query) use($parame){
                $query->where('realname', 'like', '%'.$parame['search']['realname'] . '%')
                    ->orWhere('mobile','like', '%'.$parame['search']['realname']. '%')
                    ->orWhere('nickname', 'like', '%'.$parame['search']['realname'] . '%');
            });
        }

        if($parame['search']['custom_value']){
            $result->where('yz_member.custom_value', 'like', '%' . $parame['search']['custom_value'] . '%');
        }

        if (!empty($parame['search']['groupid'])) {
            $result->where('yz_member.group_id', $parame['search']['groupid']);
        }

        if (!empty($parame['search']['level'])) {
            $result->where('yz_member.level_id', $parame['search']['level']);
        }

        if ($parame['search']['isblack'] != '') {
            $result->where('yz_member.is_black', $parame['search']['isblack']);
        }

        if ($parame['search']['isagent'] != '') {
            $result->where('yz_member.is_agent', $parame['search']['isagent']);
        }

        //余额区间搜索
        if ($parame['search']['min_credit2']) {
            $result = $result->where($credit, '>', $parame['search']['min_credit2']);
        }
        if ($parame['search']['max_credit2']) {
            $result = $result->where($credit, '<', $parame['search']['max_credit2']);
        }

        if ($parame['search']['followed'] != '') {
            $result = $result->whereHas('hasOneFans', function ($q2) use ($parame) {
                $q2->where('follow', $parame['search']['followed']);
            });
        }


        $result = $result->with(['yzMember' => function ($query) {
            return $query->select(['member_id', 'parent_id', 'inviter', 'is_agent', 'group_id', 'level_id', 'is_black', 'withdraw_mobile', 'is_old'])
                ->with(['group' => function ($query1) {
                    return $query1->select(['id', 'group_name'])->uniacid();
                }, 'level' => function ($query2) {
                    return $query2->select(['id', 'level_name'])->uniacid();
                }, 'agent' => function ($query3) {
                    return $query3->select(['uid', 'avatar', 'nickname','mobile'])->uniacid();
                }]);
        }, 'hasOneFans' => function ($query4) {
            return $query4->select(['uid', 'follow as followed'])->uniacid();
        }, 'hasOneOrder' => function ($query5) {
            return $query5->selectRaw('uid, count(uid) as total, sum(price) as sum')
                ->uniacid()
                ->where('status', Order::COMPLETE)
                ->groupBy('uid');
        },'hasOneMiniApp', 'hasOneUnique', 'hasOneWechat', 'hasOneDouyin','yzMember.agent']);

        //判断支付宝插件
        if (file_exists(base_path('plugins/alipay-onekey-login'))) {
            $result->with('hasOneAlipay');
        }

        $result->whereNull('yz_member_del_log.member_id')
            ->whereNull('yz_member.deleted_at')
            ->orderBy('yz_member.member_id', 'desc');

        return $result;
    }

    /**
     * 获取会员关系链资料申请
     *
     * @return mixed
     */
    public static function getMembersToApply($filters)
    {
        $filters['referee_id'] = [];
        if ($filters['referee'] == '1' && $filters['referee_info']) {
            $query = self::select(['uid'])
                ->uniacid()
                ->searchLike($filters['referee_info'])
                ->get();

            if (!empty($query)) {
                $data = $query->toArray();

                foreach ($data as $item) {
                    $filters['referee_id'][] = $item['uid'];
                }
            }
        }

        $query = self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile']);
        $query->uniacid();

        if (isset($filters['member'])) {
            $query->searchLike($filters['member']);
        }

        $query->whereHas('yzMember', function ($query) use ($filters) {
            if (isset($filters['searchtime']) && $filters['searchtime'] == 1) {
                if ($filters['times']) {
                    $range = [strtotime($filters['times']['start']), strtotime($filters['times']['end'])];
                    $query = $query->whereBetween('apply_time', $range);
                }
            }

            if ($filters['referee'] == '0') {
                $query->where('parent_id', $filters['referee']);
            } elseif ($filters['referee'] == '1' && !empty($filters['referee_id'])) {
                $query->whereIn('parent_id', $filters['referee_id']);
            }

            $query->where('status', 1);
        });

        $query->with(['yzMember' => function ($query) {
            return $query->select(['member_id', 'parent_id', 'apply_time'])
                ->with(['agent' => function ($query3) {
                    return $query3->select(['uid', 'avatar', 'nickname']);
                }]);
        }])
            ->orderBy('uid', 'desc');
        return $query;
    }

    /**
     * 推广下线
     *
     * @param $request
     * @return mixed
     */
    public static function getAgentInfoByMemberId($request)
    {
        $query = self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2'])
            ->uniacid();

        if ($request->keyword) {
            $query->searchLike($request->keyword);
        }

        $query->whereHas('yzMember', function ($query) use ($request) {
            $query->where('parent_id', $request->id);

            if ($request->aid) {
                $query->where('member_id', $request->aid);
            }

            if ($request->status != '') {
                $query->where('status', $request->status);
            }

            if ($request->isblack != '') {
                $query->where('is_black', $request->isblack);
            }

        });

        if ($request->followed != '') {
            $query->whereHas('hasOneFans', function ($jq) use ($request) {
                $jq->where('follow', $request->followed);
            });
        }

        $query->with(['yzMember' => function ($query) {
            return $query->select(['member_id', 'parent_id', 'is_agent', 'group_id', 'level_id', 'is_black', 'status', 'inviter'])->with(['agent' => function ($query) {
                return $query->select(['uid', 'avatar', 'nickname']);
            }]);
        }, 'hasOneFans' => function ($query) {
            return $query->select(['uid', 'follow as followed']);
        }
        ])
            ->orderBy('uid', 'desc');

        return $query;
    }

    public static function getQueueAllMembersInfo($uniacid, $limit = 0, $offset = 0)
    {
        $result = self::select(['mc_members.uid', 'mc_mapping_fans.openid', 'mc_members.uniacid'])
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
            ->join('mc_mapping_fans', 'mc_members.uid', '=', 'mc_mapping_fans.uid')
            ->whereDoesntHave('hasOneMemberUnique')
            ->where('mc_members.uniacid', $uniacid);

        if ($limit > 0) {
            $result = $result->offset($offset)->limit($limit)->orderBy('mc_members.uid', 'desc');
        }

        return $result;
    }

    public static function getAllMembersInfosByQueue($uniacid, $limit = 0, $offset = 0)
    {
        $result = self::select(['yz_member.member_id', 'yz_member.parent_id'])
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
            ->where('mc_members.uniacid', $uniacid)
            ->whereNull('yz_member.deleted_at');

        if ($limit > 0) {
            $result = $result->offset($offset)->limit($limit)->orderBy('yz_member.member_id', 'asc');
        }

        return $result;
    }

    public function chkRelationByMemberIdAndParentId()
    {
        return self::select(['member_id', 'parent_id'])
            ->uniacid()
            ->join('yz_member', function ($join) {
                $join->on('member_id', '=', 'uid')
                    ->on('member_id', '=', 'parent_id')
                    ->whereNull('deleted_at');
            })
            ->distinct()
            ->get();
    }

    /**
     * 获取待处理的原始节点数据
     *
     * 必须实现
     *
     * return \Illuminate\Support\Collection
     */
    public function getTreeAllNodes($uniacid)
    {
        return self::select(['member_id', 'parent_id'])
            ->join('yz_member', function ($join) {
                $join->on('uid', '=', 'member_id')
                    ->whereNull('deleted_at');
            })
            ->where('mc_members.uniacid', $uniacid)
            ->distinct()
            ->get();
    }

    public function chkRelationData()
    {
        $uniacid = \YunShop::app()->uniacid;

        $this->_allNodes = collect([]);
        $error = [];

        $m_relation = $this->chkRelationByMemberIdAndParentId();

        if (!is_null($m_relation)) {
            foreach ($m_relation as $m) {
                $error[] = $m->parent_id;
            }
        }

        if (!empty($error)) {
            dd($error);
        }

        $memberInfo = $this->getTreeAllNodes($uniacid);

        if ($memberInfo->isEmpty()) {
            \Log::debug('----is empty-----');
            return;
        }

        foreach ($memberInfo as $item) {
            $this->_allNodes->put($item->member_id, $item);
        }

        \Log::debug('--------queue synRun -----');

        foreach ($memberInfo as $key => $val) {
            $this->filter = [];

            \Log::debug('--------foreach start------', $val->member_id);
            $this->chkNodeParents($uniacid, $val->member_id);
        }

        if (file_exists(storage_path("logs/parenterror.log"))) {
            $error = file_get_contents(storage_path("logs/parenterror.log"));

            $error = array_unique(explode(',', $error));

            foreach ($error as $val) {
                if (!empty($val)) {
                    echo $val;
                }
            }
        }
    }

    /**
     * 后台查看会员详情（不判断黑名单）
     * @param $id
     * @return mixed
     */
    public static function getMemberDetailBlackById($id)
    {
        return self::select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime','gender',
            'birthyear','birthmonth','birthday','credit1', 'credit2'])
            ->uniacid()
            ->where('uid', $id)
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['yzMember' => function ($query) {
                return $query->select(['member_id', 'parent_id', 'is_agent', 'group_id', 'level_id', 'is_black', 'alipayname', 'alipay', 'content',
                    'status', 'custom_value', 'validity', 'member_form', 'withdraw_mobile','wechat','invite_code',
                    'province_name','city_name','area_name','address'])
                    ->with([ 'agent' => function ($query3) {
                        return $query3->select(['uid', 'avatar', 'nickname']);
                    }]);
            }, 'hasOneFans' => function ($query2) {
                return $query2->where('uniacid', \YunShop::app()->uniacid)->select(['uid','openid','follow AS followed']);
            }, 'hasOneOrder' => function ($query5) {
                return $query5->selectRaw('uid, count(uid) as total, sum(price) as sum')
                    ->uniacid()
                    ->where('status', 3)
                    ->groupBy('uid');
            },'hasOneMiniApp' => function ($query6) {
                return $query6->where('uniacid', \YunShop::app()->uniacid)->select(['member_id','openid']);
            }])
            ->first();
    }
}