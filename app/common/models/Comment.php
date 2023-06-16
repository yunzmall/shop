<?php
namespace app\common\models;

use app\backend\modules\goods\services\CommentService;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/27
 * Time: 下午5:07
 */

use Illuminate\Support\Facades\DB;

class Comment extends BaseModel
{
    const not_audit = 0;//不需要审核
    const pass_audit = 1;//通过审核
    const wait_audit = 2;//等待审核

    public $attributes = ['type' => 1];

    protected $casts = [
        'score_latitude' => 'json'
    ];
    
    use SoftDeletes;
    public $table = 'yz_comment';


    public $TypeName;

    protected $appends = ['type_name'];

    protected $guarded = [''];
    protected $fillable = [''];


    public static function getOrderGoodsComment()
    {
        return self::uniacid();
    }



    public static function getReplyById($id)
    {
        return self::uniacid()
            ->where('comment_id', $id)
            ->where('uid', '<>', DB::raw('reply_id'))
            ->orderBy('created_at', 'asc')
            ->get();
    }


//    public function getReplyAttribute()
//    {
//        if (!isset($this->Reply)) {
//            $reply['data'] = static::getReplyById($this->id);
//            $reply['count'] = $reply['data']->count('id');
//            $this->Reply = $reply;
//        }
//        return $this->Reply;
//    }
//
//    public function getAppendAttribute()
//    {
//        if (!isset($this->Append)) {
//            $append['data'] = static::getAppendById($this->id);
//            $append['count'] = $append['data']->count('id');
//            $this->Append = $append;
//        }
//        return $this->Append;
//    }

    public static function getAppendById($id)
    {
        return self::uniacid()
            ->where('comment_id', $id)
            ->where('uid', DB::raw('reply_id'))
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getTypeNameAttribute()
    {
        if (!isset($this->TypeName)) {

            $this->TypeName = CommentService::getTypeName($this->type);
        }
        return $this->TypeName;
    }

    public function hasManyReply()
    {
        return $this->hasMany(self::class);
    }

    public function hasManyAppend()
    {
        return $this->hasMany(self::class);
    }

    public function hasOneGoods(){
        return $this->hasOne(Goods::class,'id','goods_id');
    }

    public function hasOneMember()
    {
        return $this->hasOne('app\common\models\Member', 'uid', 'uid');
    }

    public function getAfterContent($comment_id)
    {
        return self::uniacid()
            ->select(['id','content','images','type'])
            ->where('comment_id',$comment_id)
            ->where('type',3)
            ->where('audit_status','!=',2)
            ->first();
    }

    /**
     * 修改主评论追评ID
     * @param int $comment_id 主评论ID
     * @param int $additional_comment_id 追评ID
     * @return bool
     */
    public static function updatedAdditionalCommentId($comment_id,$additional_comment_id)
    {
        return self::where('id', $comment_id)->update(['additional_comment_id' => $additional_comment_id]);
    }
}