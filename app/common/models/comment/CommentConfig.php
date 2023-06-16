<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/10
 * Time: 17:14
 */
namespace app\common\models\comment;

use Illuminate\Database\Eloquent\SoftDeletes;
use app\common\models\BaseModel;

class CommentConfig extends BaseModel
{
    use SoftDeletes;
    public $table = 'yz_comment_config';

    public $timestamps = true;

    protected $guarded = [];

    /**
     * @param string $column
     * @return mixed
     */
    public static function getSetConfig($column = '')
    {
        if ($column) {
            return static::uniacid()->first()->$column;
        } else {
            return static::uniacid()->first();
        }
    }

    //评分纬度
    public static function isScoreLatitude(): bool
    {
        return self::getSetConfig('is_score_latitude') ?? false;
    }
}