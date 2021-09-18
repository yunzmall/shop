<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/20
 * Time: 3:32 PM
 */

namespace app\framework\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Illuminate\Log\Writer;

abstract class BaseLog
{
    protected $logDir = '';
    protected $days = 7;
    /**
     * @var Writer;
     */
    protected $log;

    public function __construct()
    {
        $this->log = new \Illuminate\Log\Logger(new Logger(config('app.env')));
      //  $this->log->useDailyFiles(storage_path() . '/'.$this->logDir, $this->days);
        $this->log->getLogger()->pushHandler(
            $handler = new RotatingFileHandler(storage_path() . '/'.$this->logDir, $this->days)
        );
        $handler->setFormatter(new LineFormatter(null,null,true,true));
    }

    abstract public function add($message, array $content = []);
    public function getLogger(){
        return $this->log;
    }
}