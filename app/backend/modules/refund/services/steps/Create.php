<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/14
 * Time: 17:00
 */

namespace app\backend\modules\refund\services\steps;


use app\common\services\steps\BaseStepFactory;

class Create  extends BaseStepFactory
{
    public function getTitle()
    {

        return '申请时间';
    }

    public function getDescription()
    {
        return $this->model->create_time->toDateTimeString();
    }

    public function getStatus()
    {
        return 'finish';
    }

    public function isShow()
    {
        return true;
    }

    public function waitStatus()
    {

    }

    public function processStatus()
    {

    }

    public function finishStatus()
    {

    }

    public function sort()
    {
        return 0;
    }
}