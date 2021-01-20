<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/15
 * Time: 14:49
 */

namespace app\backend\modules\refund\services\steps;

use app\common\models\refund\RefundApply;
use app\common\services\steps\BaseStepFactory;

class Resend  extends BaseStepFactory
{
    public function getTitle()
    {
        switch ($this->model->status) {
            case RefundApply::WAIT_RECEIVE_RESEND_GOODS:
                $name = '等待用户收货';
                break;
            default:
                $name = '待商家发货';
        }

        return $name;
    }

    public function getDescription()
    {
        if ($this->finishStatus() && !is_null($this->model->send_time)) {
            return $this->model->send_time->toDateTimeString();
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
        return $this->model->status < RefundApply::WAIT_RECEIVE_RESEND_GOODS || !is_null($this->model->send_time);
    }

    public function waitStatus()
    {
        return $this->model->status < RefundApply::WAIT_RESEND_GOODS;
    }

    public function processStatus()
    {
        return RefundApply::WAIT_RECEIVE_RETURN_GOODS <= $this->model->status && $this->model->status < RefundApply::WAIT_RECEIVE_RESEND_GOODS;
    }

    public function finishStatus()
    {
        return !is_null($this->model->send_time);
    }

    public function sort()
    {
        return 30;
    }
}