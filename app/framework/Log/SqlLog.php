<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/20
 * Time: 2:57 PM
 */

namespace app\framework\Log;

class SqlLog extends BaseLog
{
    protected $logDir = 'logs/sql/sql.log';
    public function add($message, array $content = [])
    {
        $this->log->debug($message, $content);
    }
}