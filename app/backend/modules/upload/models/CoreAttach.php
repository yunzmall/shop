<?php
/**
 * Author:
 * Date: 2017/6/6
 * Time: 下午9:09
 */

namespace  app\backend\modules\upload\models;

use app\common\models\BaseModel;
use app\platform\modules\application\models\CoreAttachTags;
use Illuminate\Support\Carbon;


class CoreAttach extends BaseModel
{


    protected $table = 'core_attachment';
    protected $guarded = [''];
    protected $hidden  = [];
    protected $appends = ['created_at','tag_name'];
    public $timestamps = false;

    const PAGE_SIZE = 33;
    // 存储在表中type字段的对应的类型
    const IMAGE_TYPE = 1;// 图片 1
    const VOICE_TYPE = 2;// 音频 2
    const VIDEO_TYPE = 3;// 视频 3


    public function scopeSearch($query, $keyword)
    {
        if ($keyword['month'] && $keyword['year']) {
            return $query->whereBetween('createtime', [
                mktime(0,0,0, $keyword['month'], 1, $keyword['year']),
                mktime(23,59,59, $keyword['month']+1, 0, $keyword['year'])
            ]);
        }
        if ($keyword['year']) {
            return $query->whereBetween('createtime', [
                mktime(0,0,0, 1, 1, $keyword['year']),
                mktime(23,59,59,12, 31, $keyword['year'])
            ]);
        }
        if ($keyword['month']) {
            return $query->whereBetween('createtime', [
                mktime(0,0,0, $keyword['month'], 1, date('Y')),
                mktime(23,59,59, $keyword['month']+1, 0, date('Y'))
            ]);
        }
        return $query;
    }

    public static function search($search)
    {
        $model = self::uniacid();
        if ($search['year'] || $search['month']) {
            $start_time = Carbon::createFromDate($search['year'], $search['month'])->startOfMonth()->timestamp;
            $end_time = Carbon::createFromDate($search['year'], $search['month'])->endOfMonth()->timestamp;
            $model->whereBetween('createtime', [$start_time, $end_time]);
        }
        if ($search['tag_id'] === '') {
            $model->where('uid', \YunShop::app()->uid);
        }
        if ($search['tag_id'] === 0) {
            $model->where('tag_id', 0);
        }
        return $model;
    }

    public function getCreatedAtAttribute()
    {
        return date('Y-m-d H:i:s',$this->attributes['createtime']);
    }

    public function getTagNameAttribute(){
        return $this->tag_id?CoreAttachTags::where('id', $this->tag_id)->value('title'):'未分组';
    }

    public function atributeNames()
    {
        
    }
    public function rules()
    {
       
    }
}