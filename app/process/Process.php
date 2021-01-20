<?php

namespace app\process;


use app\framework\Entity\SimpleEntity;
use app\process\models\ProcessModel;

class Process extends SimpleEntity
{

    public function __construct($pid)
    {
        $this->model = new ProcessModel(['pid' => $pid]);
    }

}