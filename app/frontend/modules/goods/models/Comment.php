<?php

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 下午2:35
 */
namespace app\frontend\modules\goods\models;


use app\common\models\comment\CommentConfig;
use Carbon\Carbon;

class Comment extends \app\common\models\Comment
{

    public $Append;

    protected $appends = ['append','type_name'];

    public static function getCommentsByGoods($goods_id,$withReply = true)
    {
        $top_sort = CommentConfig::getSetConfig('top_sort');
        $with = [
            'hasOneMember' => function ($query) {
                $query->with(['yzMember' => function ($query2) {
                    $query2->select('member_id', 'level_id');
                }])->select('uid');
            }
        ];

        if ($withReply) {
            $with['hasManyReply'] = function ($query) {
                return $query->where('type', 2)
                    ->where('is_show', 1)
                    ->orderBy('created_at', 'asc');
            };
        }

        if (!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('frontend_comment_with'))) {
            foreach ($event_arr as $v) {
                $class = array_get($v, 'class');
                $function = array_get($v, 'function');
                $res = $class::$function();
                foreach ($res as $vv) {
                    $with[$vv['key']] = $vv['value'];
                }
            }
        }

        //FIELD 倒序才能让特殊排序（置顶）排前面，但是里面的顺序也会改变，因此需要反转一下
        if ($top_sort == 'desc') {
            $top_sort = 'asc';
        } else {
            $top_sort = 'desc';
        }

        $topCommentIds = self::uniacid()
            ->where('goods_id', $goods_id)
            ->where('comment_id', 0)
            ->where('is_top',1)
            ->orderBy('created_at',$top_sort)
            ->pluck('id');

        $model = self::select(
            'id', 'order_id', 'goods_id', 'uid', 'nick_name', 'head_img_url', 'content', 'level',
            'images', 'created_at', 'type', 'additional_comment_id','is_show','is_top','audit_status','has_default_good_reputation','order_goods_id','level_set')
            ->uniacid()
            ->with($with)
            ->where(function ($query) {
                $query->where('has_default_good_reputation',0)
                    ->orWhere(function($query2){
                        $query2->where('has_default_good_reputation',1)->where('additional_comment_id','!=',0);//默认好评的评价需要追评才显示
                    });
            })
            ->where('audit_status','!=', self::wait_audit)//审核中不显示
            ->where('is_show', 1)
            ->where('goods_id', $goods_id)
            ->where('comment_id', 0);

        if ($topCommentIds->isNotEmpty()) {
            //特殊排序放最前，为了置顶功能
            $model->orderByRaw('FIELD(id, ' . implode(',', $topCommentIds->toArray()) . ') desc');
        }

        //正常的排序放后面
        return $model->orderBy('created_at', 'desc');
    }

    //获取所有评价数量（包括默认好评）
    public static function getAllCommentTotal($goods_id)
    {
        return self::uniacid()
            ->where('goods_id',$goods_id)
            ->where('comment_id',0)
            ->where('audit_status','!=', self::wait_audit)
            ->where('is_show', 1)
            ->count('id');
    }

    //前端’默认好评已隐藏‘字样  需要默认好评已追评不进行该好评字样判断的话就加上 ->where('additional_comment_id',0)
    public static function isShowGoodReputationText($goods_id)
    {
        return self::uniacid()
            ->selectRaw('1')
            ->where('goods_id',$goods_id)
            ->where('has_default_good_reputation',1)
            ->where('additional_comment_id',0)
            ->first();
    }


    public function getAppendAttribute()
    {
        if (!isset($this->Append)) {
            $this->Append = static::getAppendById($this->id);
            if ($this->additional_comment_id) {
                $this->Append->diff_date = self::diffDate($this->created_at->timestamp,$this->Append->created_at->timestamp);//有存在0天的情况
            }
        }
        return $this->Append;
    }

    public static function getAppendById($id)
    {

        return self::uniacid()
            ->select('content','images','comment_id','type','created_at')
            ->where('comment_id', $id)
            ->where('type', 3)
            ->where('audit_status', '!=', self::wait_audit)
            ->orderBy('created_at', 'asc')
            ->first();//todo type=3为追评，每条评论只能有一条追评，不需要get
    }

    public static function getOrderGoodsComment()
    {
        return self::uniacid()
            ->with(['hasManyReply'=>function ($query) {
                return $query->where('type', 2)
                    ->where('is_show',1)
                    ->orderBy('created_at', 'asc');
            }]);
    }


    public function hasOneOrderGoods()
    {
        return $this->hasOne('app\common\models\OrderGoods', 'order_id', 'order_id');
    }
    public function hasOneGoods(){
        return $this->hasOne('app\common\models\Goods','id','goods_id');
    }

    public function hasOneLiveInstallComment(){
        return $this->hasOne(\Yunshop\LiveInstall\models\Comment::class,'comment_id','id');
    }

    public function diffDate(int $commentTime,int $additionalCommentTime)
    {
        $old_time = Carbon::parse(date('Y-m-d H:i:s',$commentTime));
        $new_time = Carbon::createFromTimestamp($additionalCommentTime);

        $dateDiff = [
            $old_time->diffInDays($new_time),//日
            $old_time->diffInMonths($new_time),//月
            $old_time->diffInYears($new_time),//年
        ];

        if ($dateDiff[2] != 0) {
            $res = [
                'value' => $dateDiff[2],
                'sku' => '年后'
            ];

        } else if($dateDiff[1] != 0) {
            $res = [
                'value' => $dateDiff[1],
                'sku' => '个月后'
            ];

        } else {
            $res = [
                'value' => $dateDiff[0],
                'sku' => '天后'
            ];
        }

        return $res;
    }

}