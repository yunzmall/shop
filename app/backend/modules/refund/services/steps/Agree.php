<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/14
 * Time: 18:19
 */

namespace app\backend\modules\refund\services\steps;


use app\common\models\refund\RefundApply;
use app\common\services\steps\BaseStepFactory;

class Agree  extends BaseStepFactory
{
    public function getTitle()
    {
        switch ($this->model->status) {
            case RefundApply::COMPLETE:
               $name = '售后(退款完成)';
                break;
            case RefundApply::CONSENSUS:
                $name = '售后(手动退款)';
                break;
            case RefundApply::CLOSE:
                $name = '售后(换货关闭)';
                break;
            default:
                $name = '售后完成';

        }

        return $name;
    }

    public function getDescription()
    {
        if ($this->finishStatus() && !is_null($this->model->refund_time)) {
            return $this->model->refund_time->toDateTimeString();
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
        return true;
    }

    public function waitStatus()
    {
        return $this->model->status < RefundApply::COMPLETE;
    }

    public function processStatus()
    {
        return false;
    }

    public function finishStatus()
    {
        return $this->model->status == RefundApply::COMPLETE ||
            $this->model->status == RefundApply::CONSENSUS ||
            $this->model->status == RefundApply::CLOSE;
    }

    public function sort()
    {
        return 99999;
    }
}