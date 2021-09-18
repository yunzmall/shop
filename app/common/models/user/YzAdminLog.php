<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 07/03/2017
 * Time: 10:40
 */

namespace app\common\models\user;


use app\common\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class YzAdminLog extends BaseModel
{
    use SoftDeletes;

    public $table = 'yz_admin_logs';

    protected $hidden = ['id'];

    /**
     * 记录列表
     * @param int $pageSize
     * @return object
     */
    public static function getPageList($pageSize, $search)
    {
        $query = static::with(['hasOneMember' => function ($q) {
            $q->select('uid', 'nickname', 'avatar');
        }]);

        //不是超级管理员区分公众号
        if(\YunShop::app()->uid !== 1){
            $query->uniacid();
        }

        //账号类型
        if ($search['remark'] != '') {
            $query->where('remark', $search['remark']);
        }

        //登录账号
        if ($search['username'] != '') {
            $query->where('username', $search['username']);
        }

        //会员ID，昵称等搜索
        if (!empty($search['member_info'])) {
            $query->whereHas('hasOneMember', function ($q) use ($search) {
                $q->where('nickname', 'like', "%{$search['member_info']}%")
                    ->orWhere('realname', 'like', "%{$search['member_info']}%")
                    ->orWhere('uid', 'like', "%{$search['member_info']}%")
                    ->orWhere('mobile', 'like', "%{$search['member_info']}%");
            });
        }

        //时间搜索：is_search_time=1 搜索时间，为0则不搜索时间
        if ($search['is_search_time'] == 1) {
            $range = [intval($search['times']['start']), intval($search['times']['end'])];
            $query->whereBetween('created_at', $range);
        }

        return $query->orderBy('created_at','desc')->paginate($pageSize);
    }

    static public function del($start, $end)
    {
        $range = [$start, $end];
        return static::whereBetween('created_at', $range);
    }

    public function hasOneMember()
    {
        return $this->hasOne(\app\common\models\Member::class, 'uid', 'member_id');
    }

}