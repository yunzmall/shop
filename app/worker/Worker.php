<?php


namespace app\worker;


use Illuminate\Support\Facades\Redis;
use Workerman\Lib\Timer;

class Worker extends \Workerman\Worker
{
    /**
     * Run all worker instances.
     *
     * @return void
     */
    public static function runAll()
    {
        static::checkSapiEnv();
        static::init();
        static::lock();
        static::parseCommand();
        static::daemonize();
        static::initWorkers();
        static::installSignal();
        static::saveMasterPid();
        static::unlock();
        static::displayUI();
        static::forkWorkers();
        static::resetStd();
        static::monitorWorkers();
    }
    /**
     * Lock.
     *
     * @return void
     */
    protected static function lock()
    {
        Redis::lock('WorkerStarting',gethostname());
    }
    /**
     * Unlock.
     *
     * @return void
     */
    protected static function unlock()
    {
        Redis::unlock('WorkerStarting');
    }

    /**
     * Init.
     *
     * @return void
     */
    protected static function init()
    {
        \set_error_handler(function ($code, $msg, $file, $line) {
            \Workerman\Worker::safeEcho("$msg in file $file on line $line\n");
        });
        // Start file.
        static::$_startFile = __FILE__;

        $unique_prefix = \str_replace('/', '_', gethostname() . static::$_startFile);

        // Pid file.
        if (empty(static::$pidFile)) {
            static::$pidFile = storage_path('logs') . "/$unique_prefix.pid";
        }

        // Log file.
        if (empty(static::$logFile)) {
            static::$logFile = storage_path('logs') . '/workerman.log';
        }
        $log_file = (string)static::$logFile;
        if (!\is_file($log_file)) {
            \touch($log_file);
            \chmod($log_file, 0622);
        }

        // State.
        static::$_status = static::STATUS_STARTING;

        // For statistics.
        static::$_globalStatistics['start_timestamp'] = \time();
        static::$_statisticsFile = \sys_get_temp_dir() . "/$unique_prefix.status";

        // Process title.
        static::setProcessTitle(static::$processTitle . ': master process  start_file=' . static::$_startFile);

        // Init data for worker id.
        static::initId();

        // Timer init.
        Timer::init();
    }

    /**
     * @param string $msg
     */
    public static function log($msg)
    {
        $msg = $msg . "\n";
        if (!static::$daemonize) {
            static::safeEcho($msg);
        }
        //todo 取消写入日志，不断重试写入导致磁盘满空间
//        \file_put_contents((string)static::$logFile, \date('Y-m-d H:i:s') . ' ' . 'pid:'
//            . (static::$_OS === \OS_TYPE_LINUX ? \posix_getpid() : 1) . ' ' . $msg, \FILE_APPEND | \LOCK_EX);

    }
}