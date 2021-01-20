<?php


namespace app\framework\Log;


class SimpleLog extends BaseLog
{
    public function __construct($name = 'simple', $days = 7)
    {
        $this->logDir = 'logs/' . $name . '.log';
        $this->days = $days;
        parent::__construct();
    }

    public function add($message, array $content = [])
    {
        $this->log->debug($message, $content);
    }

    public function debug($message, $content = [])
    {
        if(!is_array($content)){
            $content = [$content];
        }
        $this->log->debug($message, $content);
    }

    public function info($message, $content = [])
    {
        if(!is_array($content)){
            $content = [$content];
        }
        $this->log->info($message, $content);
    }

    public function error($message, $content = [])
    {
        if(!is_array($content)){
            $content = [$content];
        }
        $this->log->error($message, $content);
    }

}