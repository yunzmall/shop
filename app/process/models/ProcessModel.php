<?php


namespace app\process\models;


use app\framework\Model\SimpleModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

/**
 * Class ProcessModel
 * @package app\process\models
 * @property int pid
 * @property int createdAt
 * @property string runningTime
 */
class ProcessModel extends SimpleModel
{
    public $attributeTypes = [
        'pid' => 'int',
        'createdAt' => 'timestamp',
        'runningTime' => 'string',
    ];
    public $attributes = [
        'pid'=>0,
        'createdAt'=>null,
        'runningTime'=>null,
    ];

    protected function getCreatedAtAttribute()
    {
        return Redis::hget('ProcessData', $this->pid);
    }
    protected function getRunningTimeAttribute()
    {
        return Carbon::createFromTimestamp($this->createdAt)->diffForHumans();
    }
}