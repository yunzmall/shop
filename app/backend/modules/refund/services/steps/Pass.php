<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/14
 * Time: 18:09
 */

namespace app\backend\modules\refund\services\steps;


use app\common\models\refund\RefundApply;
use app\common\services\steps\BaseStepFactory;

class Pass extends BaseStepFactory
{
    public function getTitle()
    {
        if ($this->finishStatus()) {
            return '售后审核通过';
        }
        return '待审核';
    }

    public function getDescription()
    {
        if ($this->finishStatus() && !is_null($this->model->operate_time)) {
            return $this->model->operate_time->toDateTimeString();
        }

        return '';
    }

    public function getStatus()
    {
        if ($this->finishStatus()) {
            return 'finish';
        } elseif ($this->processStatus()) {
            return 'process';
        } elseif ($this->waitStatus()) {
            return 'wait';
        }

        return 'error';
    }

    public function isShow()
    {
        return $this->model->status == RefundApply::WAIT_CHECK || !is_null($this->model->operate_time);
    }

    public function waitStatus()
    {
        return true;
    }

    public function processStatus()
    {
        return $this->model->status == RefundApply::WAIT_CHECK;
    }

    public function finishStatus()
    {
        return !is_null($this->model->operate_time);
    }

    public function sort()
    {
        return 10;
    }
}