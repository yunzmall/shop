<?php

namespace app\backend\modules\job\models;

use app\common\models\BaseModel;

class FailedJob extends BaseModel
{
    protected $table = 'failed_jobs';
}