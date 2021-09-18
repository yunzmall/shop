<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/15
 * Time: 14:44
 */

namespace app\backend\modules\refund\services\steps;

use app\common\models\refund\RefundApply;
use app\common\services\steps\BaseStepFactory;

class UserReturn  extends BaseStepFactory
{
    public function getTitle()
    {
        switch ($this->model->status) {
            case RefundApply::WAIT_RECEIVE_RETURN_GOODS:
                $name = '客户退货寄回';
                break;
            default:
                $name = '待客户退货';
        }

        return $name;
    }

    public function getDescription()
    {
        if ($this->finishStatus() && !is_null($this->model->return_time) ) {
            return $this->model->return_time->toDateTimeString();
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
        return  (RefundApply::WAIT_CHECK < $this->model->status && $this->model->status < RefundApply::WAIT_RETURN_GOODS) || !is_null($this->model->return_time);
    }

    public function waitStatus()
    {
        return $this->model->status < RefundApply::WAIT_RETURN_GOODS;
    }

    public function processStatus()
    {
        return $this->model->status == RefundApply::WAIT_RETURN_GOODS;
    }

    public function finishStatus()
    {
        return !is_null($this->model->return_time);
    }

    public function sort()
    {
        return 20;
    }
}