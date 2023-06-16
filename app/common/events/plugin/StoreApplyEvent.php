<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/12/20
 * Time: 11:19
 */

namespace app\common\events\plugin;

use app\common\events\Event;

class StoreApplyEvent extends Event
{
    protected $apply_model;

    public function __construct($apply_model)
    {
        $this->apply_model = $apply_model;
    }

    public function getApplyModel()
    {
        return $this->apply_model;
    }
}