<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/17/017
 * Time: 15:05
 */

namespace app\platform\modules\application\models;

use app\common\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use app\backend\modules\upload\models\CoreAttach as WQCoreAttach;

class CoreAttachTags extends BaseModel
{
    use SoftDeletes;

    protected $table = 'yz_core_attachment_tags';
    protected $guarded = [''];
    protected $hidden  = ['deleted_at', 'updated_at'];
    public $timestamps = true;
    protected $datas = ['deleted_at'];

    // 存储在表中sourceType字段的对应的类型
    const IMAGE_TYPE = 1;// 图片 1
    const VOICE_TYPE = 2;// 音频 2
    const VIDEO_TYPE = 3;// 视频 3

    public function hasManySource()
    {
        if (config('app.framework') == 'platform')
        {
            return $this->hasMany(CoreAttach::class, 'tag_id', 'id');
        }
        else
        {
            return $this->hasMany(WQCoreAttach::class, 'tag_id', 'id');

        }

    }


}